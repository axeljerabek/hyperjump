import os
import re
import json

# --- KONFIGURATION DER PLATZHALTER -------------------------------------------
# ERSETZEN SIE HIER DIE PLATZHALTER-KEYS DURCH IHRE ECHTEN, PRIVATEN WERTE!
# Das Skript sucht nach dem "Key" und ersetzt ihn durch den "Value".

REPLACEMENTS = {
    # 1. SICHERHEIT & PASSWÖRTER (config.php)
    '$2y$10$asdfaDfasdfasdfasdfasdfasdfasdfasdffaD.': 'PLACEHOLDER_ADMIN_PASSWORD_HASH', 
    'mein@privatemail.de': 'YOUR_ANONYMOUS_EMAIL@example.com',
    
    # 2. API-KEYS & STANDORTE
    'abcdef0123456789deadbeef': 'YOUR_WEATHER_API_KEY_HERE', 
    'lat=48.137154&lon=11.576124': 'lat=52.520008&lon=13.404954',

    # 3. PRIVATE DOMAINS, HOSTS & IPS (Alle gefundenen axel.jerabek.fi und interner Host)
    
    # NEU: Verbleibende Links in data.php, index.php, README.md
    'https://axel.jerabek.fi/system/system.php': 'https://www.google.com/search?q=system+status',
    'https://axel.jerabek.fi/webcam.php': 'https://www.google.com/search?q=webcam+test',
    'https://axel.jerabek.fi/chat.php': 'https://www.google.com/search?q=chat+application',
    'https://axel.jerabek.fi/index_orig.html': 'https://www.google.com/search?q=homepage+archive',
    'https://axel.jerabek.fi/flowworks/': 'https://www.google.com/search?q=flowworks',
    
    # Links für die Heizung
    'https://axel.jerabek.fi/heating_bed2/index.php': 'https://www.google.com/search?q=heating+control+1',
    'https://axel.jerabek.fi/heating_mid2/index.php': 'https://www.google.com/search?q=heating+control+2',
    'https://axel.jerabek.fi/heating_eat2/index.php': 'https://www.google.com/search?q=heating+control+3',
    'https://axel.jerabek.fi/heating_kitchen2/index.php': 'https://www.google.com/search?q=heating+control+4',
    'https://axel.jerabek.fi/heating_kids2/index.php': 'https://www.google.com/search?q=heating+control+5',
    'https://axel.jerabek.fi/heating_living2/index.php': 'https://www.google.com/search?q=heating+control+6',

    # Links in index.php und README.md
    'https://axel.jerabek.fi/hyperjump_demo/': 'https://your-public-demo.com/',
    'https://axel.jerabek.fi': 'https://github.com/YOUR_ANON_USER/hyperjump', # index.php Header-Link
    
    # Git-Links und Hosts
    'https://github.com/axeljerabek/hyperjump': 'https://github.com/YOUR_ANON_USER/hyperjump', 
    'https://github.com/axeljerabek/hyperjump.git': 'https://github.com/YOUR_ANON_USER/hyperjump.git',
    'lin-axel': 'YOUR_GENERIC_HOST_NAME', 

    # Alte Platzhalter (aus früheren Läufen)
    'https://your-private-sub.domain/climate/weather.php': 'https://www.google.com/search?q=weather',
    'https://your-private-sub.domain/weatherforecast/': 'https://www.google.com/search?q=weather+forecast',
    'https://your-private-mushrooms.domain/': 'https://www.google.com/search?q=mushrooms',
    'https://your-private-home.domain:9443': 'https://www.google.com/search?q=portainer',
    'http://192.168.178.25:4500': 'https://www.google.com/search?q=crowdsec+internal', 
    'http://192.168.178.25:5380': 'https://www.google.com/search?q=dns+admin',
    'https://app.crowdsec.net/security-engines': 'https://app.crowdsec.net/YOUR_ENGINE_ID',
    
    # 4. SENSIBLE BUBLLE-NAMEN (data.php, order.json)
    '"immich server"': '"image host"',
    '"router"': '"network device"',
    '"repeater"': '"WiFi extender"',
    '"dawarich"': '"RSS reader"',
    '"nextcloud (intern)"': '"cloud storage"',
    '"immich gpu (intern)"': '"image processor"',
    '"heating bedroom"': '"heating room 1"',
    '"heating hallway"': '"heating room 2"',
    '"heating dining room"': '"heating room 3"',
    '"heating kitchen"': '"heating room 4"',
    '"heating kids"': '"heating room 5"',
    '"heating living"': '"heating room 6"',
    '"webcam tester"': '"camera tool"',
    '"home tracking"': '"location tracker"',
}

# --- DATEIEN ZUR VERARBEITUNG ---
FILES_TO_PROCESS = [
    'admin.php',
    'data.php',
    'get_weather.php',
    'add_link.php',
    'save_admin.php',
    'save_order.php',
    'login.php',
    'config.php',
    'index.php',
    'README.md', # Auch hier müssen URLs ersetzt werden
]

# --- HAUPTPROGRAMM -----------------------------------------------------------

def anonymize_code_files(replacements, files):
    """Führt die Ersetzung in PHP/JS/MD-Dateien durch."""
    total_replacements = 0
    print("--- 1. Anonymisiere Code-Dateien (PHP/JS/MD) ---")

    for filename in files:
        if not os.path.exists(filename):
            continue
            
        print(f"Verarbeite: {filename}")
        
        try:
            with open(filename, 'r', encoding='utf-8') as f:
                content = f.read()
            
            file_replacements = 0

            for old_val, new_val in replacements.items():
                # Ersetzt alle Vorkommen des alten Wertes durch den neuen
                content, count = re.subn(re.escape(old_val), new_val, content)
                file_replacements += count
            
            if file_replacements > 0:
                total_replacements += file_replacements
                print(f"  -> {file_replacements} Ersetzungen durchgeführt.")
                
                with open(filename, 'w', encoding='utf-8') as f:
                    f.write(content)
            else:
                print("  -> Keine Änderungen notwendig.")
        
        except Exception as e:
            print(f"  -> FEHLER beim Verarbeiten von {filename}: {e}")

    return total_replacements

def anonymize_plugins(replacements):
    """Fügt Plugin-Dateien zur Verarbeitung hinzu und führt die Ersetzung durch."""
    plugin_files = []
    print("\n--- 2. Suche und anonymisiere Plugin-Dateien ---")
    
    for root, _, files in os.walk('plugins'):
        for file in files:
            if file.endswith(('.js', '.php', '.css')):
                plugin_files.append(os.path.join(root, file))

    return anonymize_code_files(replacements, plugin_files)


def anonymize_json_files():
    """Setzt order.json und weather_cache.json auf einen leeren Zustand zurück."""
    print("\n--- 3. Bereinige JSON-Laufzeitdaten ---")
    json_files = ['order.json', 'weather_cache.json']
    
    for filename in json_files:
        if os.path.exists(filename):
            print(f"Setze {filename} zurück auf leere Struktur...")
            if filename == 'order.json':
                # order.json wird durch eine leere Konfiguration ersetzt
                content = '{"category_order": [], "bubble_orders": {}}'
                
            elif filename == 'weather_cache.json':
                # Weather Cache wird geleert
                content = '{}'
            
            try:
                with open(filename, 'w', encoding='utf-8') as f:
                    f.write(content)
                print("  -> Datei erfolgreich anonymisiert (geleert/ersetzt).")
            except Exception as e:
                 print(f"  -> FEHLER beim Zurücksetzen von {filename}: {e}")
        else:
            print(f"Skippe {filename}: Datei existiert nicht.")


# Skript ausführen
if __name__ == "__main__":
    
    # Füge automatisch alle Plugin-Dateien zur Verarbeitung hinzu
    total_plugin_replacements = anonymize_plugins(REPLACEMENTS)

    # Verarbeite die Hauptdateien
    total_main_replacements = anonymize_code_files(REPLACEMENTS, FILES_TO_PROCESS)
    
    # Bereinige die JSON-Dateien
    anonymize_json_files()
    
    print("-" * 40)
    print(f"✅ Anonymisierung abgeschlossen.")
    print(f"Insgesamt {total_main_replacements + total_plugin_replacements} Ersetzungen in Code-Dateien.")
    print("Bitte führen Sie jetzt die Schritte zur Git-Vorbereitung durch, um die Historie zu schützen.")
