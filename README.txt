KA-HULAAN XAMPP WEB GAME
Ready-to-run PHP + MySQL project

PROJECT SUMMARY
Ka-Hulaan is a browser-based digital learning game for elderly users. It includes three game modes:
1. 4 Clues 1 Word
2. Tile Selection
3. Mini Crossword

Each game mode has 50 unique stored scenarios in the MySQL database, for a total of 150 scenarios.

FEATURES INCLUDED
- Sign up and login system
- Password hashing using PHP password_hash
- Session-based authentication
- MySQL database
- 150 seeded scenarios
- 4 clues per scenario
- Tile mode with 4 solution tiles
- Crossword-style letter bank mode
- Score system
- Attempts tracking
- Per-user progress tracking
- Reward and badge system
- Profile page with statistics
- Leaderboard
- Reset progress function
- Responsive elderly-friendly UI
- High contrast colors
- Large buttons and readable cards

INSTALLATION ON XAMPP
1. Extract this folder into:
   C:\xampp\htdocs\

2. Make sure the folder name is exactly:
   ka_hulaan_xampp

3. Open XAMPP Control Panel.
   Start Apache and MySQL.

4. Open your browser and go to:
   http://localhost/phpmyadmin

5. Click Import.
   Select this file:
   ka_hulaan_xampp/database/ka_hulaan.sql

6. Click Go and wait for the import to finish.

7. Open the web app:
   http://localhost/ka_hulaan_xampp

8. Create a new account using the Sign Up page.

DATABASE CONFIGURATION
Default XAMPP database settings are already used:
Host: localhost
Database: ka_hulaan_game
Username: root
Password: blank

If your MySQL has a password, edit:
config/config.php

IMPORTANT FILES
- config/config.php: database connection and app constants
- database/ka_hulaan.sql: database schema and 150 scenarios
- assets/css/style.css: full UI design
- assets/js/game.js: game logic and API calls
- api/get_scenario.php: loads next unanswered scenario
- api/submit_answer.php: checks answer, saves score, awards badges
- dashboard.php: main game selection page
- profile.php: user stats and rewards

GAME MODE NOTES
4 Clues 1 Word:
The user sees four clues and builds the answer using letter buttons.

Tile Selection:
The user reads a scenario and chooses the correct answer from four large tiles.

Mini Crossword:
The user completes a crossword-style answer box using a clue and letter bank.

TROUBLESHOOTING
If the page says database connection failed:
- Check if MySQL is running in XAMPP.
- Check if ka_hulaan_game database exists.
- Check config/config.php for DB_USER and DB_PASS.

If CSS or JS does not load:
- Make sure the folder name is ka_hulaan_xampp.
- If you renamed the folder, update BASE_URL in config/config.php.
