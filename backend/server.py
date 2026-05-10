from fastapi import FastAPI, APIRouter
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import create_engine, Column, String, Text
from sqlalchemy.orm import declarative_base, sessionmaker
from dotenv import load_dotenv
from pydantic import BaseModel, Field
from typing import Optional
from pathlib import Path
import uuid
import os

# Load .env
ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / ".env")

# ENV
DATABASE_URL = os.getenv("DATABASE_URL")
CORS_ORIGINS = os.getenv("CORS_ORIGINS", "*").split(",")

# Database
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(bind=engine)

Base = declarative_base()

# FastAPI
app = FastAPI(title="Aerol Colt API")
api_router = APIRouter(prefix="/api")

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Database Model
class LeadDB(Base):
    __tablename__ = "leads"

    id = Column(String(100), primary_key=True, index=True)
    name = Column(String(120))
    phone = Column(String(50))
    email = Column(String(160))
    message = Column(Text)

# Create tables
Base.metadata.create_all(bind=engine)

# Pydantic Model
class LeadCreate(BaseModel):
    name: str = Field(..., min_length=2)
    phone: str
    email: Optional[str] = None
    message: Optional[str] = None

# Routes
@api_router.get("/")
async def root():
    return {"message": "API Working"}

@api_router.post("/leads")
async def create_lead(payload: LeadCreate):

    db = SessionLocal()

    lead = LeadDB(
        id=str(uuid.uuid4()),
        name=payload.name,
        phone=payload.phone,
        email=payload.email,
        message=payload.message
    )

    db.add(lead)
    db.commit()

    db.close()

    return {
        "success": True,
        "message": "Lead saved"
    }

@api_router.get("/leads")
async def get_leads():

    db = SessionLocal()

    leads = db.query(LeadDB).all()

    data = []

    for lead in leads:
        data.append({
            "id": lead.id,
            "name": lead.name,
            "phone": lead.phone,
            "email": lead.email,
            "message": lead.message
        })

    db.close()

    return data

app.include_router(api_router)