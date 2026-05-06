from fastapi import FastAPI, APIRouter, HTTPException
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware

import os
import logging
import uuid
import smtplib

from pathlib import Path
from datetime import datetime, timezone
from typing import Optional, Literal

from pydantic import BaseModel, Field, ConfigDict

from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

from motor.motor_asyncio import AsyncIOMotorClient


# =========================================================
# LOAD ENV
# =========================================================

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / ".env")


# =========================================================
# APP SETUP
# =========================================================

app = FastAPI(title="Aerol Colt Security Systems API")

api_router = APIRouter(prefix="/api")


# =========================================================
# LOGGING
# =========================================================

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s"
)

logger = logging.getLogger(__name__)


# =========================================================
# MONGODB
# =========================================================

MONGO_URL = os.environ["MONGO_URL"]

client = AsyncIOMotorClient(MONGO_URL)

db = client["aerolcolt_db"]


# =========================================================
# MODELS
# =========================================================

class LeadCreate(BaseModel):
    name: str = Field(..., min_length=2, max_length=120)
    phone: str = Field(..., min_length=5, max_length=40)
    email: Optional[str] = Field(default=None, max_length=160)
    message: Optional[str] = Field(default=None, max_length=2000)

    source: Literal[
        "site_assessment",
        "custom_quote",
        "contact_form"
    ] = "contact_form"


class Lead(BaseModel):

    model_config = ConfigDict(extra="ignore")

    id: str = Field(default_factory=lambda: str(uuid.uuid4()))

    name: str
    phone: str

    email: Optional[str] = None
    message: Optional[str] = None

    source: str = "contact_form"

    created_at: datetime = Field(
        default_factory=lambda: datetime.now(timezone.utc)
    )


# =========================================================
# ROOT ROUTE
# =========================================================

@api_router.get("/")
async def root():

    return {
        "message": "Aerol Colt Security Systems API is online"
    }


# =========================================================
# EMAIL FUNCTION
# =========================================================

def send_lead_email(lead: Lead):

    smtp_server = os.environ["SMTP_SERVER"]
    smtp_port = int(os.environ["SMTP_PORT"])

    smtp_email = os.environ["SMTP_EMAIL"]
    smtp_password = os.environ["SMTP_PASSWORD"]

    recipient_email = os.environ["RECIPIENT_EMAIL"]

    subject = f"New Lead • {lead.name}"

    html_body = f"""
    <html>
    <body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">

        <div style="
            max-width:600px;
            margin:auto;
            background:white;
            padding:30px;
            border-radius:12px;
            border:1px solid #e5e5e5;
        ">

            <h2 style="margin-top:0;">
                New Website Lead
            </h2>

            <p style="color:#666;">
                A new inquiry has been submitted through the website.
            </p>

            <hr style="margin:20px 0;">

            <p>
                <strong>Name:</strong><br>
                {lead.name}
            </p>

            <p>
                <strong>Phone:</strong><br>
                {lead.phone}
            </p>

            <p>
                <strong>Email:</strong><br>
                {lead.email or "Not provided"}
            </p>

            <p>
                <strong>Lead Source:</strong><br>
                {lead.source}
            </p>

            <p>
                <strong>Message:</strong><br>
                {lead.message or "No message"}
            </p>

            <hr style="margin:20px 0;">

            <p style="font-size:12px; color:#999;">
                Lead ID: {lead.id}
            </p>

        </div>

    </body>
    </html>
    """

    msg = MIMEMultipart("alternative")

    msg["From"] = smtp_email
    msg["To"] = recipient_email
    msg["Subject"] = subject

    msg.attach(MIMEText(html_body, "html"))

    server = smtplib.SMTP_SSL(
        smtp_server,
        smtp_port
    )

    server.login(
        smtp_email,
        smtp_password
    )

    server.sendmail(
        smtp_email,
        recipient_email,
        msg.as_string()
    )

    server.quit()


# =========================================================
# CREATE LEAD
# =========================================================

@api_router.post("/leads", response_model=Lead)
async def create_lead(payload: LeadCreate):

    try:

        # CREATE LEAD OBJECT
        lead = Lead(**payload.model_dump())

        # STORE IN MONGODB
        lead_data = lead.model_dump()

        lead_data["created_at"] = (
            lead.created_at.isoformat()
        )

        await db.leads.insert_one(lead_data)

        logger.info(
            f"Lead stored in MongoDB: {lead.id}"
        )

        # SEND EMAIL
        send_lead_email(lead)

        logger.info(
            f"Lead email sent successfully: {lead.id}"
        )

        return lead

    except Exception as e:

        logger.error(
            f"Lead submission failed: {str(e)}"
        )

        raise HTTPException(
            status_code=500,
            detail="Failed to submit lead"
        )


# =========================================================
# INCLUDE ROUTER
# =========================================================

app.include_router(api_router)


# =========================================================
# CORS
# =========================================================

app.add_middleware(
    CORSMiddleware,

    allow_credentials=True,

    allow_origins=os.environ.get(
        "CORS_ORIGINS",
        "*"
    ).split(","),

    allow_methods=["*"],
    allow_headers=["*"],
)