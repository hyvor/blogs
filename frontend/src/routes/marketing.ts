import { Crisp } from "crisp-sdk-web";
import { browser, dev } from "$app/environment";

function initCrisp() {
    Crisp.configure("1cab78fb-4baf-497e-a10f-00a3b12cfcfe");
}

export function setUpMarketing() {
    if (dev)
        return;
    if (browser) {
        initCrisp();
    }
}