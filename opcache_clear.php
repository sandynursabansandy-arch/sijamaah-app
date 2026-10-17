<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared.\n";
} else {
    echo "OPcache not available.\n";
}
echo "PHP version: " . phpversion() . "\n";
echo "OPcache enabled: " . (ini_get('opcache.enable') ? 'yes' : 'no') . "\n";
echo "validate_timestamps: " . (ini_get('opcache.validate_timestamps') ?? 'not set') . "\n";
echo "revalidate_freq: " . (ini_get('opcache.revalidate_freq') ?? 'not set') . "\n";
