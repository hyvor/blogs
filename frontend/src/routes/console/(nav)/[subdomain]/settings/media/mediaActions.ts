import consoleApi from "../../../../lib/consoleApi";

export interface VerifyResults {
    write: boolean;
    read: boolean;
    //visibility: boolean;
    //public_access: boolean;
    delete: boolean;
    errors: {
        write?: string;
        read?: string;
        //visibility?: string;
        //public_access?: string;
        delete?: string;
    };
}

export interface S3Storage {
    endpoint_url: string;
    bucket_name: string;
    access_key: string;
    secret_key: string;
    region: string | null;
    path_prefix: string | null;
    path_style_access: boolean;
    cdn_url: string | null;
}

export function getS3Storage() {
    return consoleApi.get<S3Storage | null>({
        endpoint: '/integrations/s3',
    })
}

export function updateS3Integration(
    endpointUrl: string,
    bucketName: string,
    accessKey: string,
    secretKey: string,
    region: string,
    pathPrefix: string,
    pathStyleAccess: boolean,
    customCdnUrl: string,
    test: boolean = false,
) {
    return consoleApi.post<VerifyResults>({
        endpoint: '/integrations/s3',
        data: {
            endpoint_url: endpointUrl,
            bucket_name: bucketName,
            access_key: accessKey,
            secret_key: secretKey,
            region: region,
            path_prefix: pathPrefix,
            path_style_access: pathStyleAccess,
            cdn_url: customCdnUrl,
            test
        }
    })
}