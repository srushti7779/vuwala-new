@echo off

:: Set a default commit message
set commitMsg=Auto commit - %date% %time%

echo [1/3] Adding changes...
git add .

echo [2/3] Committing with message: %commitMsg%
git commit -m "%commitMsg%"

echo [3/3] Pushing to origin main...
git push origin main

echo Done!
