const fs = require('fs');

const { env } = process;

// Utility to assist in decoding a packed JSON variable.
function read_base64_json(varName) {
    try {
        return Buffer.from(env[varName], 'base64').toString();
    } catch (err) {
        throw new Error(`no ${varName} environment variable`);
    }
}

// An encoded JSON object.
const env_variables = read_base64_json('PLATFORM_VARIABLES');

fs.closeSync(fs.openSync('.env', 'w'));
fs.appendFileSync('.env', `PLATFORM_VARS=${env_variables.replace(/\s+/g, '')}`);
