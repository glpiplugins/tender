#!/bin/bash

# Temporäre Datei erstellen
tempfile=$(mktemp)

# Verzeichnisse definieren, die durchsucht werden sollen
search_dirs="../src ../templates"

# Alle relevanten Dateien in den angegebenen Verzeichnissen finden
find $search_dirs -type f \( -name "*.html.twig" -o -name "*.php" -o -name "*.twig" \) | while read file; do
  # Zeilen mit __() Funktionen finden
  grep -n '__(' "$file" | while read -r line; do
    # Zeilennummer extrahieren
    lineno=$(echo "$line" | cut -d: -f1)
    # Inhalt der Zeile extrahieren
    content=$(echo "$line" | cut -d: -f2-)
    # msgid aus der __() Funktion extrahieren
    msgid=$(echo "$content" | sed -n "s/.*__ *( *['\"]\([^'\"]*\)['\"].*/\1/p")
    # Wenn msgid nicht leer ist, in temporäre Datei schreiben
    if [ -n "$msgid" ]; then
      echo -e "$msgid\t$file:$lineno" >> "$tempfile"
    fi
  done
done

# Temporäre Datei sortieren
sort "$tempfile" > "$tempfile.sorted"

# Duplikate zusammenfassen und .po Datei erstellen
awk -F '\t' '
BEGIN {
  prev_msgid = ""
  locations = ""
}
{
  if ($1 == prev_msgid) {
    # Gleiche msgid, Location hinzufügen
    locations = locations " " $2
  } else {
    # Neue msgid, vorherige ausgeben
    if (prev_msgid != "") {
      # Locations formatieren
      n = split(locations, loc_array, " ")
      for (i = 1; i <= n; i++) {
        if (i % 5 == 1) {
          printf "#:"
        }
        printf " %s", loc_array[i]
        if (i % 5 == 0 || i == n) {
          printf "\n"
        }
      }
      # msgid und msgstr ausgeben
      printf "msgid \"%s\"\n", prev_msgid
      printf "msgstr \"\"\n\n"
    }
    # Neue msgid starten
    prev_msgid = $1
    locations = $2
  }
}
END {
  # Letzte msgid ausgeben
  if (prev_msgid != "") {
    n = split(locations, loc_array, " ")
    for (i = 1; i <= n; i++) {
      if (i % 5 == 1) {
        printf "#:"
      }
      printf " %s", loc_array[i]
      if (i % 5 == 0 || i == n) {
        printf "\n"
      }
    }
    printf "msgid \"%s\"\n", prev_msgid
    printf "msgstr \"\"\n\n"
  }
}' "$tempfile.sorted" > messages.po

# Temporäre Dateien entfernen
rm "$tempfile" "$tempfile.sorted"

echo "Die .po Datei wurde erstellt: messages.po"
