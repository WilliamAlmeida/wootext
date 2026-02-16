#!/bin/sh
script_name="laravel-automations 49"


# Set default values for Laravel automations
: "${AUTORUN_ENABLED:=false}"

if [ "$DISABLE_DEFAULT_CONFIG" = "false" ]; then
    # Check to see if an Artisan file exists and assume it means Laravel is configured.
    if [ -f "$APP_BASE_DIR/artisan" ] && [ "$AUTORUN_ENABLED" = "true" ]; then
        echo "Checking for Laravel automations..."

        ############################################################################
        # key:generate
        ############################################################################
        if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then
            echo "❌ APP_KEY not defined! Please set the APP_KEY in your .env file or docker-compose.yml to continue."
            echo "You can generate a new one by running: php artisan key:generate --show"
            echo "Generating a temporary one..."
            GENERATED_KEY=$(php "$APP_BASE_DIR/artisan" key:generate --ansi --show)
            echo "🔑 APP_KEY generated: $GENERATED_KEY"
            echo "⚠️  Copy this APP_KEY and set it in your .env file or docker-compose.yml to avoid future problems."
            exit 1
        else
            echo "✅ APP_KEY already defined."
        fi

        ############################################################################
        # npm install and npm run build for assets
        ############################################################################

        if [ "${AUTORUN_LARAVEL_NPM_BUILD_ASSETS:=false}" = "true" ]; then
            echo "🚀 Installing dependencies and compiling JS and CSS assets..."
            if [ -f "$APP_BASE_DIR/package.json" ]; then
                echo "📦 Running npm install..."
                cd "$APP_BASE_DIR" && npm install
                echo "🔨 Running npm run build..."
                cd "$APP_BASE_DIR" && npm run build
            else
                echo "⚠️ package.json file not found. Skipping assets compilation."
            fi
        fi

        ############################################################################
        # artisan icon:cache
        ############################################################################

        if [ "${AUTORUN_LARAVEL_ICON_CACHE:=true}" = "true" ]; then
            echo "🚀 Caching Laravel icon..."
            php "$APP_BASE_DIR/artisan" icon:cache
        fi
    fi
else
    if [ "$LOG_OUTPUT_LEVEL" = "debug" ]; then
        echo "👉 $script_name: DISABLE_DEFAULT_CONFIG does not equal 'false', so automations will NOT be performed."
    fi
fi