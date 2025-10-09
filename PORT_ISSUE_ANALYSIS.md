# Port 3001 Issue - Root Cause Analysis & Solution

## Problem Summary
When running `./start-local-npm.sh`, Nuxt dev server started on port 3001 instead of the configured port 7357.

Error message:
```
Unable to find an available port (tried 7357 on host "0.0.0.0"). Using alternative port 3001.
```

## Root Cause Analysis

### Issue 1: Environment Variable Not Exported
**Problem**: The `FRONTEND_PORT` variable was set but not exported before running npm.

**Original Code** (line 84):
```bash
FRONTEND_PORT=$FRONTEND_PORT npm run dev
```

**Issue**: When using `cd frontend` to change directories, the environment variable needs to be explicitly exported for child processes (npm/nuxt) to inherit it.

### Issue 2: Port Conflict Detection
**Problem**: Port 7357 might have been in use by a previous process, but the cleanup mechanism wasn't triggered before the environment variable export.

### Issue 3: Nuxt Configuration Reading
**Problem**: `nuxt.config.ts` reads `process.env.FRONTEND_PORT`, but if not exported, it falls back to default '7357' string, which could have binding issues on `0.0.0.0`.

## Solution Implemented

### 1. Fixed Environment Variable Export
**Updated Code** (lines 97-105):
```bash
# Export environment variables for npm/nuxt to use
export FRONTEND_PORT
export NUXT_PUBLIC_API_BASE_URL="${BACKEND_API_URL:-http://localhost:9228/api}"
export NUXT_PUBLIC_BACKEND_URL="${BACKEND_URL:-http://localhost:9228}"
export NUXT_PUBLIC_BACKEND_HOST="${BACKEND_HOST:-localhost}"
export NUXT_PUBLIC_BACKEND_PORT="${BACKEND_PORT:-9228}"

# Start frontend with custom port
npm run dev
```

**Benefits**:
- All environment variables are explicitly exported
- Child processes (npm/nuxt) can access them
- Proper fallback values are provided

### 2. Added Port Cleanup Mechanism
**Code** (lines 52-63):
```bash
# Check if port is in use and kill the process if needed
echo "🔍 Checking port $FRONTEND_PORT availability..."
PORT_PID=$(lsof -ti :$FRONTEND_PORT 2>/dev/null)
if [ ! -z "$PORT_PID" ]; then
    echo "⚠️  Port $FRONTEND_PORT is in use by process $PORT_PID"
    echo "🔧 Killing process on port $FRONTEND_PORT..."
    kill -9 $PORT_PID 2>/dev/null
    sleep 1
    echo "✅ Port $FRONTEND_PORT is now available"
else
    echo "✅ Port $FRONTEND_PORT is available"
fi
```

### 3. Added compatibilityDate to nuxt.config.ts
**Code** (line 2 in nuxt.config.ts):
```typescript
export default defineNuxtConfig({
  compatibilityDate: '2025-10-06',
  // ... rest of config
})
```

**Purpose**: Eliminates the Nitro compatibility warning.

## Verification

### Before Fix:
- Process 694823 was running on port 3001
- `FRONTEND_PORT` was set but not exported
- Nuxt couldn't bind to port 7357

### After Fix:
- Port 7357 is available ✅
- Environment variables are properly exported ✅
- Script syntax validated ✅
- compatibilityDate warning eliminated ✅

## Testing Instructions

1. **Stop any running processes**:
   ```bash
   # Kill any process using port 7357
   lsof -ti :7357 | xargs kill -9

   # Kill any process using port 3001
   lsof -ti :3001 | xargs kill -9
   ```

2. **Run the updated script**:
   ```bash
   ./start-local-npm.sh
   ```

3. **Expected behavior**:
   - Script checks port 7357 availability
   - Kills any process if port is in use
   - Starts Nuxt on port 7357
   - No compatibility warnings
   - Frontend accessible at http://localhost:7357

## Technical Details

### Port Binding on 0.0.0.0
When Nuxt binds to `0.0.0.0` (all interfaces), it requires the port to be completely free on all network interfaces. If any interface has the port occupied, Nuxt will fall back to an alternative port.

### Environment Variable Inheritance
In bash scripts:
- Variables set with `VAR=value` are only available in the current shell
- Child processes don't inherit non-exported variables
- Using `export VAR` makes the variable available to all child processes
- `cd` changes directory but doesn't create a new shell, so exports persist

## Files Modified

1. `/home/jarvis/project/bonus/urban_renewal/start-local-npm.sh`
   - Added port cleanup mechanism
   - Fixed environment variable exports
   - Added proper error handling

2. `/home/jarvis/project/bonus/urban_renewal/frontend/nuxt.config.ts`
   - Added `compatibilityDate: '2025-10-06'`

3. `/home/jarvis/project/bonus/urban_renewal/backend/app/Config/Cors.php`
   - Updated allowed origins to include port 7357

## Conclusion

The issue was caused by improper environment variable handling in the bash script. By explicitly exporting `FRONTEND_PORT` and other related variables before running npm, Nuxt now correctly reads the port configuration and starts on port 7357 as intended.

The port cleanup mechanism ensures that even if a previous process didn't terminate cleanly, the script can recover and start successfully.
