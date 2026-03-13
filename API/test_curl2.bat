@echo off
setlocal enabledelayedexpansion

:: ====================================================
::  test_curl2.bat - Tests curl.exe avec saisie URL
::  Aux Claviers Citoyens
:: ====================================================

:: Verifier que curl.exe est disponible
where curl.exe >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo.
    echo  [ERREUR] curl.exe introuvable dans le PATH.
    echo  Solutions :
    echo    1. Windows 10 v1803+ : curl.exe est dans C:\Windows\System32\
    echo       Verifiez avec : where curl
    echo    2. Sinon telechargez curl : https://curl.se/windows/
    echo    3. Ajoutez le dossier de curl.exe a la variable PATH
    echo.
    pause
    exit /b 1
)

:: ====================================================
:: SAISIE DE L'URL
:: ====================================================
echo.
echo  ====================================================
echo   Tests curl2 - API Aux Claviers Citoyens
echo  ====================================================
echo.
echo  URL de base de l'API
echo  (exemple : http://localhost/Aux-Claviers-Citoyens/API)
echo  Laissez vide pour utiliser la valeur par defaut.
echo.
set /p INPUT_URL=  URL :
if "!INPUT_URL!"=="" set INPUT_URL=http://localhost/Aux-Claviers-Citoyens/API
set BASE=!INPUT_URL!
echo.
echo  URL utilisee : !BASE!
echo.

:: ====================================================
:: TEST DE CONNECTIVITE
:: ====================================================
echo  Verification de la connexion au serveur...
curl.exe -s -o nul -w "%%{http_code}" "!BASE!" > "%TEMP%\acc2_ping.tmp" 2>nul
set /p PINGCODE= < "%TEMP%\acc2_ping.tmp"
del /q "%TEMP%\acc2_ping.tmp" 2>nul

if "!PINGCODE!"=="000" (
    echo.
    echo  [ARRET] Serveur inaccessible sur : !BASE!
    echo  Causes possibles :
    echo    - WAMP / Apache non demarre
    echo    - Mauvaise URL saisie
    echo    - Pare-feu bloquant le port 80
    echo.
    pause
    exit /b 1
)
echo  Serveur repond ^(HTTP !PINGCODE!^) - OK
echo.

:: ====================================================
:: INITIALISATION
:: ====================================================
for /f "tokens=*" %%D in ('powershell -NoProfile -Command "Get-Date -Format 'yyyy-MM-dd_HH-mm-ss'"') do set DT=%%D

set LOG=%~dp0log2_%DT%.txt
set BODY=%TEMP%\acc2_body.tmp
set CODE=%TEMP%\acc2_code.tmp
set REQ=%TEMP%\acc2_req.json

set TOKEN=
set T_ID=0
set TEAM_ID=0
set TEAM2_ID=0
set R_ID=0
set M_ID=0
set USER_ID=0
set /a TOTAL=0
set /a OK=0
set /a FAIL=0

(
echo ====================================================
echo  Tests curl2 - API Aux Claviers Citoyens
echo  %DT%
echo  URL : !BASE!
echo ====================================================
echo.
) > "%LOG%"

echo  Log : %LOG%
echo.

goto :TESTS

:: ====================================================
:: :EVAL - evalue un appel curl et logue le resultat
::
:: Variables attendues avant l'appel :
::   DESC      = description du test
::   METHOD    = methode HTTP (GET, POST, PUT, DELETE)
::   ENDPOINT  = chemin appele (ex: /tournaments/3)
::
:: Variable produite apres l'appel :
::   LASTCODE  = code HTTP recu
:: ====================================================
:EVAL
for /f "usebackq tokens=*" %%C in ("%CODE%") do set LASTCODE=%%C
if "!LASTCODE!"=="" set LASTCODE=0
set /a TOTAL+=1

:: --- Signification du code HTTP ---
set HTTP_MEANING=Code inconnu
if "!LASTCODE!"=="0"   set HTTP_MEANING=Serveur injoignable ^(WAMP demarre ? URL correcte ?^)
if "!LASTCODE!"=="200" set HTTP_MEANING=Succes
if "!LASTCODE!"=="201" set HTTP_MEANING=Ressource creee avec succes
if "!LASTCODE!"=="204" set HTTP_MEANING=Succes sans contenu retourne
if "!LASTCODE!"=="301" set HTTP_MEANING=Redirection permanente ^(verifier l'URL^)
if "!LASTCODE!"=="302" set HTTP_MEANING=Redirection temporaire ^(verifier l'URL^)
if "!LASTCODE!"=="400" set HTTP_MEANING=Requete invalide - champs manquants ou mal formates
if "!LASTCODE!"=="401" set HTTP_MEANING=Non authentifie - token manquant, invalide ou expire
if "!LASTCODE!"=="403" set HTTP_MEANING=Acces interdit - appel direct sans passer par index.php
if "!LASTCODE!"=="404" set HTTP_MEANING=Ressource introuvable - ID inexistant ou route incorrecte
if "!LASTCODE!"=="405" set HTTP_MEANING=Methode HTTP non autorisee sur cette route
if "!LASTCODE!"=="409" set HTTP_MEANING=Conflit - doublon ^(nom deja utilise^)
if "!LASTCODE!"=="422" set HTTP_MEANING=Donnees semantiquement invalides
if "!LASTCODE!"=="500" set HTTP_MEANING=Erreur interne serveur - voir logs Apache/PHP dans WAMP

:: --- OK ou ERREUR ---
set IS_OK=0
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 set IS_OK=1

if !IS_OK!==1 (
    set /a OK+=1
    echo [OK]     !DESC! ^(HTTP !LASTCODE!^) >> "%LOG%"
    echo [OK]     !DESC!
) else (
    set /a FAIL+=1

    :: Extraire error + error_description + message depuis le JSON
    for /f "tokens=*" %%E in ('powershell -NoProfile -Command "$raw=try{Get-Content '%BODY%' -Raw}catch{''}; try{ $j=$raw|ConvertFrom-Json; $p=@(); if($j.error){$p+='error='+$j.error}; if($j.error_description){$p+='desc='+$j.error_description}; if($j.message){$p+='message='+$j.message}; if($p.Count-eq 0){$p+='(aucun champ error/message dans la reponse)'}; $p-join' | ' }catch{ if($raw-and $raw.Length-gt 0){'Reponse non-JSON : '+$raw.Substring(0,[Math]::Min(80,$raw.Length))}else{'Reponse vide - serveur injoignable ou URL incorrecte'} }"') do set ERRMSG=%%E

    :: Corps brut (100 premiers caracteres)
    for /f "tokens=*" %%R in ('powershell -NoProfile -Command "try{$c=(Get-Content '%BODY%' -Raw).Trim(); if($c.Length-gt 100){'Corps: '+$c.Substring(0,100)+'...'}elseif($c.Length-gt 0){'Corps: '+$c}else{'Corps: (vide)'}}catch{'Corps: (lecture impossible)'}"') do set RAWBODY=%%R

    :: Conseil selon le code
    set CONSEIL=
    if "!LASTCODE!"=="0"   set CONSEIL=Verifiez que WAMP est demarre et que l'URL est correcte
    if "!LASTCODE!"=="401" set CONSEIL=Le token JWT est absent, expire ou invalide. Relancez le login.
    if "!LASTCODE!"=="403" set CONSEIL=Le fichier PHP est appele directement. Passez par index.php.
    if "!LASTCODE!"=="404" set CONSEIL=L'ID utilise ^(!T_ID! / !TEAM_ID! / !R_ID! / !M_ID!^) n'existe peut-etre pas en base.
    if "!LASTCODE!"=="409" set CONSEIL=Une ressource avec ce nom existe deja. Videz la base ou changez le nom.
    if "!LASTCODE!"=="500" set CONSEIL=Ouvrez les logs Apache dans WAMP ^(icone WAMP > Apache > error.log^)

    echo [ERREUR] !DESC! >> "%LOG%"
    echo          HTTP !LASTCODE! : !HTTP_MEANING! >> "%LOG%"
    echo          !ERRMSG! >> "%LOG%"
    echo          !RAWBODY! >> "%LOG%"
    if not "!CONSEIL!"=="" echo          Conseil : !CONSEIL! >> "%LOG%"
    echo. >> "%LOG%"

    echo [ERREUR] !DESC!
    echo          HTTP !LASTCODE! : !HTTP_MEANING!
    echo          !ERRMSG!
    if not "!CONSEIL!"=="" echo          Conseil : !CONSEIL!
)
goto :eof

:: ====================================================
:SECTION
echo. >> "%LOG%"
echo ==================================================== >> "%LOG%"
echo  %~1 >> "%LOG%"
echo ==================================================== >> "%LOG%"
echo.
echo  ==================================================
echo   %~1
echo  ==================================================
goto :eof

:CRUD_HEADER
echo   --- %~1 --- >> "%LOG%"
echo   --- %~1 ---
goto :eof

:WARN_ID
:: Avertit si un ID est a 0 (etape precedente a echoue)
if "%~2"=="0" (
    echo   [AVERT]  %~1 = 0 : l'etape CREATE precedente a echoue, ce test va probablement echouer. >> "%LOG%"
    echo   [AVERT]  %~1 = 0 - CREATE precedent echoue, test risque d'echouer
)
goto :eof

:: ====================================================
:TESTS
:: ====================================================

call :SECTION "token.php / register.php / me.php  (Authentification)"
call :CRUD_HEADER "CREATE - inscription"

echo {"name":"Toto","email":"toto@net.fr","password":"toto"}> "%REQ%"
curl.exe -s -X POST "%BASE%/auth/register" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
for /f "usebackq tokens=*" %%C in ("%CODE%") do set REGCODE=%%C

if "!REGCODE!"=="201" (
    set /a TOTAL+=1 & set /a OK+=1
    echo [OK]     [CREATE] POST /auth/register - Utilisateur cree ^(201^) >> "%LOG%"
    echo [OK]     [CREATE] POST /auth/register - Utilisateur cree
)
if "!REGCODE!"=="409" (
    echo [INFO]   [CREATE] POST /auth/register - Utilisateur deja existant ^(409^) >> "%LOG%"
    echo [INFO]   [CREATE] POST /auth/register - Utilisateur deja existant
)
if not "!REGCODE!"=="201" if not "!REGCODE!"=="409" (
    set /a TOTAL+=1 & set /a FAIL+=1
    echo [ERREUR] [CREATE] POST /auth/register ^(HTTP !REGCODE!^) - Inscription echouee >> "%LOG%"
    echo [ERREUR] [CREATE] POST /auth/register - Inscription echouee ^(HTTP !REGCODE!^)
)

call :CRUD_HEADER "CREATE - login (token JWT)"

echo {"email":"toto@net.fr","password":"toto"}> "%REQ%"
curl.exe -s -X POST "%BASE%/auth/token" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /auth/token (login)
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%T in ('powershell -NoProfile -Command "(Get-Content '%BODY%' -Raw|ConvertFrom-Json).access_token"') do set TOKEN=%%T
)
if "!TOKEN!"=="" (
    echo.
    echo  [ARRET] Impossible d'obtenir le token JWT.
    echo  Causes possibles :
    echo    - L'utilisateur toto@net.fr n'existe pas en base
    echo    - Le mot de passe est incorrect
    echo    - La base de donnees est vide ^(lancez test_data.php^)
    echo    - L'URL est incorrecte : !BASE!
    echo.
    echo  [ARRET] Token JWT non obtenu - tests annules >> "%LOG%"
    goto :RECAP
)

call :CRUD_HEADER "READ"

curl.exe -s -X GET "%BASE%/auth/me" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /auth/me
call :EVAL


:: ====================================================
call :SECTION "rest_user.php  (Utilisateurs)"
:: ====================================================

call :CRUD_HEADER "CREATE"

echo {"name":"Jean Dupont","email":"jean@test.fr","password":"pass123"}> "%REQ%"
curl.exe -s -X POST "%BASE%/users" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /users
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%I in ('powershell -NoProfile -Command "try{(Get-Content '%BODY%' -Raw|ConvertFrom-Json).id}catch{0}"') do set USER_ID=%%I
)

call :CRUD_HEADER "READ"
call :WARN_ID "USER_ID" "!USER_ID!"

curl.exe -s -X GET "%BASE%/users" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /users
call :EVAL

curl.exe -s -X GET "%BASE%/users?id=!USER_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /users?id=!USER_ID!
call :EVAL

call :CRUD_HEADER "UPDATE"
call :WARN_ID "USER_ID" "!USER_ID!"

echo {"name":"Jean Modifie","email":"jean@test.fr"}> "%REQ%"
curl.exe -s -X PUT "%BASE%/users?id=!USER_ID!" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[UPDATE] PUT  /users?id=!USER_ID! (infos)
call :EVAL

echo {"password":"nouveaupass"}> "%REQ%"
curl.exe -s -X PUT "%BASE%/users?id=!USER_ID!" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[UPDATE] PUT  /users?id=!USER_ID! (mot de passe)
call :EVAL

call :CRUD_HEADER "DELETE"
call :WARN_ID "USER_ID" "!USER_ID!"

curl.exe -s -X DELETE "%BASE%/users?id=!USER_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[DELETE] DELETE /users?id=!USER_ID!
call :EVAL


:: ====================================================
call :SECTION "rest_tournament.php  (Tournois)"
:: ====================================================

call :CRUD_HEADER "CREATE"

echo {"name":"TournoiTest","game":"GameTest","teamcount":4}> "%REQ%"
curl.exe -s -X POST "%BASE%/tournaments" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /tournaments
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%I in ('powershell -NoProfile -Command "try{(Get-Content '%BODY%' -Raw|ConvertFrom-Json).id}catch{0}"') do set T_ID=%%I
)

call :CRUD_HEADER "READ"

curl.exe -s -X GET "%BASE%/tournaments" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments
call :EVAL

call :WARN_ID "T_ID" "!T_ID!"
curl.exe -s -X GET "%BASE%/tournaments/!T_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!
call :EVAL

call :CRUD_HEADER "UPDATE"
call :WARN_ID "T_ID" "!T_ID!"

echo {"name":"TournoiModifie","game":"GameTest","status":"En cours"}> "%REQ%"
curl.exe -s -X PUT "%BASE%/tournaments/!T_ID!" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[UPDATE] PUT  /tournaments/!T_ID!
call :EVAL


:: ====================================================
call :SECTION "rest_team.php  (Equipes)"
:: ====================================================

call :CRUD_HEADER "CREATE"
call :WARN_ID "T_ID" "!T_ID!"

echo {"name":"TeamAlpha"}> "%REQ%"
curl.exe -s -X POST "%BASE%/tournaments/!T_ID!/teams" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /tournaments/!T_ID!/teams (equipe 1)
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%I in ('powershell -NoProfile -Command "try{(Get-Content '%BODY%' -Raw|ConvertFrom-Json).data.id}catch{0}"') do set TEAM_ID=%%I
)

echo {"name":"TeamBeta"}> "%REQ%"
curl.exe -s -X POST "%BASE%/tournaments/!T_ID!/teams" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /tournaments/!T_ID!/teams (equipe 2)
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%I in ('powershell -NoProfile -Command "try{(Get-Content '%BODY%' -Raw|ConvertFrom-Json).data.id}catch{0}"') do set TEAM2_ID=%%I
)

call :CRUD_HEADER "READ"
call :WARN_ID "TEAM_ID" "!TEAM_ID!"

curl.exe -s -X GET "%BASE%/tournaments/!T_ID!/teams" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!/teams
call :EVAL

curl.exe -s -X GET "%BASE%/tournaments/!T_ID!/teams/!TEAM_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!/teams/!TEAM_ID!
call :EVAL

call :CRUD_HEADER "UPDATE"
call :WARN_ID "TEAM_ID" "!TEAM_ID!"

echo {"name":"TeamAlphaModifiee"}> "%REQ%"
curl.exe -s -X PUT "%BASE%/tournaments/!T_ID!/teams/!TEAM_ID!" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[UPDATE] PUT  /tournaments/!T_ID!/teams/!TEAM_ID!
call :EVAL


:: ====================================================
call :SECTION "rest_round.php  (Rounds)"
:: ====================================================

call :CRUD_HEADER "CREATE"
call :WARN_ID "T_ID" "!T_ID!"

echo {"name":"QuartsDeFinale"}> "%REQ%"
curl.exe -s -X POST "%BASE%/tournaments/!T_ID!/rounds" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /tournaments/!T_ID!/rounds
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%I in ('powershell -NoProfile -Command "try{(Get-Content '%BODY%' -Raw|ConvertFrom-Json).data.id}catch{0}"') do set R_ID=%%I
)

call :CRUD_HEADER "READ"
call :WARN_ID "R_ID" "!R_ID!"

curl.exe -s -X GET "%BASE%/tournaments/!T_ID!/rounds" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!/rounds
call :EVAL

curl.exe -s -X GET "%BASE%/tournaments/!T_ID!/rounds/!R_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!/rounds/!R_ID!
call :EVAL

curl.exe -s -X GET "%BASE%/rounds/!R_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /rounds/!R_ID! (route directe)
call :EVAL

call :CRUD_HEADER "UPDATE"
call :WARN_ID "R_ID" "!R_ID!"

echo {"name":"DemiFinale"}> "%REQ%"
curl.exe -s -X PUT "%BASE%/tournaments/!T_ID!/rounds/!R_ID!" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[UPDATE] PUT  /tournaments/!T_ID!/rounds/!R_ID!
call :EVAL


:: ====================================================
call :SECTION "rest_match.php  (Matchs)"
:: ====================================================

call :CRUD_HEADER "CREATE"
call :WARN_ID "TEAM_ID"  "!TEAM_ID!"
call :WARN_ID "TEAM2_ID" "!TEAM2_ID!"
call :WARN_ID "R_ID"     "!R_ID!"

echo {"team1_id":!TEAM_ID!,"team2_id":!TEAM2_ID!,"idR":!R_ID!}> "%REQ%"
curl.exe -s -X POST "%BASE%/tournaments/!T_ID!/matches" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[CREATE] POST /tournaments/!T_ID!/matches (team1=!TEAM_ID! team2=!TEAM2_ID! round=!R_ID!)
call :EVAL
if !LASTCODE! GEQ 200 if !LASTCODE! LEQ 299 (
    for /f "tokens=*" %%I in ('powershell -NoProfile -Command "try{(Get-Content '%BODY%' -Raw|ConvertFrom-Json).data.id}catch{0}"') do set M_ID=%%I
)

call :CRUD_HEADER "READ"
call :WARN_ID "M_ID" "!M_ID!"

curl.exe -s -X GET "%BASE%/tournaments/!T_ID!/matches" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!/matches
call :EVAL

curl.exe -s -X GET "%BASE%/tournaments/!T_ID!/matches/!M_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /tournaments/!T_ID!/matches/!M_ID!
call :EVAL

curl.exe -s -X GET "%BASE%/match/!M_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[READ]   GET  /match/!M_ID! (route directe)
call :EVAL

call :CRUD_HEADER "UPDATE"
call :WARN_ID "M_ID" "!M_ID!"

echo {"teamId":!TEAM_ID!,"teamPoint":3}> "%REQ%"
curl.exe -s -X PUT "%BASE%/tournaments/!T_ID!/matches/!M_ID!/point" -H "Authorization: Bearer !TOKEN!" -H "Content-Type: application/json" -d @"%REQ%" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[UPDATE] PUT  /tournaments/!T_ID!/matches/!M_ID!/point (equipe !TEAM_ID! -> 3 pts)
call :EVAL

call :CRUD_HEADER "DELETE"
call :WARN_ID "M_ID" "!M_ID!"

curl.exe -s -X DELETE "%BASE%/tournaments/!T_ID!/matches/!M_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[DELETE] DELETE /tournaments/!T_ID!/matches/!M_ID!
call :EVAL


:: ====================================================
call :SECTION "Suppressions finales (nettoyage)"
:: ====================================================

call :CRUD_HEADER "DELETE  rest_round.php"
call :WARN_ID "R_ID" "!R_ID!"

curl.exe -s -X DELETE "%BASE%/tournaments/!T_ID!/rounds/!R_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[DELETE] DELETE /tournaments/!T_ID!/rounds/!R_ID!
call :EVAL

call :CRUD_HEADER "DELETE  rest_team.php"
call :WARN_ID "TEAM_ID" "!TEAM_ID!"

curl.exe -s -X DELETE "%BASE%/tournaments/!T_ID!/teams/!TEAM_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[DELETE] DELETE /tournaments/!T_ID!/teams/!TEAM_ID! (equipe 1)
call :EVAL

curl.exe -s -X DELETE "%BASE%/tournaments/!T_ID!/teams/!TEAM2_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[DELETE] DELETE /tournaments/!T_ID!/teams/!TEAM2_ID! (equipe 2)
call :EVAL

call :CRUD_HEADER "DELETE  rest_tournament.php"
call :WARN_ID "T_ID" "!T_ID!"

curl.exe -s -X DELETE "%BASE%/tournaments/!T_ID!" -H "Authorization: Bearer !TOKEN!" -o "%BODY%" -w "%%{http_code}" > "%CODE%" 2>nul
set DESC=[DELETE] DELETE /tournaments/!T_ID!
call :EVAL


:: ====================================================
:RECAP
:: ====================================================
echo. >> "%LOG%"
echo ==================================================== >> "%LOG%"
echo  URL testee  : !BASE! >> "%LOG%"
echo  Resultats   : !TOTAL! test(s) - !OK! OK - !FAIL! erreur(s) >> "%LOG%"
echo ==================================================== >> "%LOG%"

echo.
echo  ====================================================
echo   URL testee  : !BASE!
echo   Resultats   : !TOTAL! test(s) - !OK! OK - !FAIL! erreur(s)
echo  ====================================================
echo.
echo  Log sauvegarde : %LOG%
echo.

for %%F in ("%BODY%" "%CODE%" "%REQ%") do if exist %%F del /q %%F 2>nul

endlocal
pause
