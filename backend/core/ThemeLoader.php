<?php

class ThemeLoader {

    public function load(array $branding): array
    {
        $theme = [];

        // Load preset
        if (!empty($branding['theme_preset'])) {

            $presetPath = ROOT . '/storage/themes/presets/' . $branding['theme_preset'] . '.json';

            if (file_exists($presetPath)) {
                $theme = json_decode(file_get_contents($presetPath), true);
            }
        }

        // Merge user overrides
        if (!empty($branding['theme_json'])) {

            $custom = json_decode($branding['theme_json'], true);

            if (is_array($custom)) {
                $theme = array_merge($theme, $custom);
            }
        }

        return $theme;
    }
}