export interface Config {
    domains: {
        app: string,
        delivery: string
    },
    limits: {
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
