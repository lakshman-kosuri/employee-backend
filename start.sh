#!/bin/bash

echo "Fixing Apache MPM issue..."

# 🔥 Remove ALL MPM configs
rm -f /etc/apache2/mods-enabled/mpm_*.load
rm -f /etc/apache2/mods-enabled/mpm_*.conf

# ✅ Enable ONLY prefork
a2enmod mpm_prefork

# Start Apache
apache2-foreground