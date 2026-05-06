@echo off

cd /d "C:\Projects\AerolColt Webpage\backend"
start cmd /k "python -m uvicorn server:app --reload"

timeout /t 3 > nul

cd /d "C:\Projects\AerolColt Webpage\frontend"
start cmd /k "npm start"