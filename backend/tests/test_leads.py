"""Tests for Aerol Colt Security Systems - Leads API"""
import os
import pytest
import requests

BASE_URL = os.environ.get('REACT_APP_BACKEND_URL', 'https://aerolcoltsecuritysystems.ae').rstrip('/')
API = f"{BASE_URL}/api"


@pytest.fixture(scope="module")
def client():
    s = requests.Session()
    s.headers.update({"Content-Type": "application/json"})
    return s


# -------- Root / Health --------
class TestRoot:
    def test_api_root(self, client):
        r = client.get(f"{API}/")
        assert r.status_code == 200
        body = r.json()
        assert "message" in body


# -------- Leads CRUD --------
class TestLeadsAPI:
    created_ids = []

    def test_create_lead_success(self, client):
        payload = {
            "name": "TEST_John Smith",
            "phone": "+971501234567",
            "email": "testjohn@example.com",
            "message": "Please contact me regarding CCTV systems.",
            "source": "contact_form",
        }
        r = client.post(f"{API}/leads", json=payload)
        assert r.status_code == 200, r.text
        data = r.json()
        assert data["name"] == payload["name"]
        assert data["phone"] == payload["phone"]
        assert data["email"] == payload["email"]
        assert data["message"] == payload["message"]
        assert data["source"] == "contact_form"
        assert "id" in data and isinstance(data["id"], str)
        assert "created_at" in data
        assert "_id" not in data
        TestLeadsAPI.created_ids.append(data["id"])

    def test_create_lead_minimal_optional_fields(self, client):
        payload = {"name": "TEST_Minimal User", "phone": "+971500000000"}
        r = client.post(f"{API}/leads", json=payload)
        assert r.status_code == 200, r.text
        data = r.json()
        assert data["email"] is None
        assert data["message"] is None
        assert data["source"] == "contact_form"
        TestLeadsAPI.created_ids.append(data["id"])

    def test_create_lead_rejects_empty_name(self, client):
        r = client.post(f"{API}/leads", json={"name": "", "phone": "+971500000001"})
        assert r.status_code == 422

    def test_create_lead_rejects_empty_phone(self, client):
        r = client.post(f"{API}/leads", json={"name": "TEST_User", "phone": ""})
        assert r.status_code == 422

    def test_create_lead_rejects_short_name(self, client):
        r = client.post(f"{API}/leads", json={"name": "A", "phone": "+971500000002"})
        assert r.status_code == 422

    def test_create_lead_rejects_invalid_source(self, client):
        r = client.post(f"{API}/leads", json={
            "name": "TEST_User", "phone": "+971500000003", "source": "invalid_src"
        })
        assert r.status_code == 422

    def test_list_leads_excludes_mongo_id_and_sorted_desc(self, client):
        r = client.get(f"{API}/leads")
        assert r.status_code == 200
        items = r.json()
        assert isinstance(items, list)
        assert len(items) >= 2
        # No _id leaked
        for it in items:
            assert "_id" not in it
            assert "id" in it
            assert "created_at" in it
        # Sorted newest first
        created_ats = [it["created_at"] for it in items]
        assert created_ats == sorted(created_ats, reverse=True)

    def test_leads_count_returns_int(self, client):
        r = client.get(f"{API}/leads/count")
        assert r.status_code == 200
        data = r.json()
        assert "count" in data
        assert isinstance(data["count"], int)
        assert data["count"] >= len(TestLeadsAPI.created_ids)

    def test_source_variants_accepted(self, client):
        for src in ["site_assessment", "custom_quote", "contact_form"]:
            r = client.post(f"{API}/leads", json={
                "name": f"TEST_Src_{src}", "phone": "+971500000099", "source": src
            })
            assert r.status_code == 200, r.text
            assert r.json()["source"] == src
            TestLeadsAPI.created_ids.append(r.json()["id"])
