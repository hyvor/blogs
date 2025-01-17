import { getConfig } from "../../../lib/config";

/**
 * @deprecated
 */
export function initPaddle() {

    return new Promise<void>((resolve, reject) => {

        const script = document.createElement('script');
        script.src = 'https://cdn.paddle.com/paddle/paddle.js';
        script.async = true;
        script.onload = () => {
            const { vendor_id, sandbox } = getConfig().services.paddle;
    
            if (sandbox) {
                (window as any).Paddle.Environment.set('sandbox');
            }
    
            (window as any).Paddle.Setup({
                vendor: vendor_id,
            });

            resolve();
        };

        script.onerror = () => {
            reject();
        }

        document.body.appendChild(script);

    });

    

}