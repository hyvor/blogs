
export interface Config {
    hyvor: {
        instance: string;
    }
    domains: {
        app: string,
        delivery: string
    },
    limits: {
        max_upload_size: number,
        max_theme_zip_size: number,
        max_asset_file_size: number
    },
    highlight_themes: string[],
    services: {
        paddle: {
            sandbox: boolean,
            vendor_id: number,
        }
    }
}

let config = {} as Config;

export function setConfig(c: Config) {
    config = c;
}

export function getConfig() {
    return config;
}


export async function loadConfig() {
    if (config.domains) {
        return;
    }

    const response = await fetch('/api/special/config');
    const data = await response.json();

    setConfig(data);
}