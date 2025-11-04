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
    console.log('test', test);
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