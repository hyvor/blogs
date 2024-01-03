import { onMount } from "svelte";
import { get } from 'svelte/store';
import { writable } from "svelte/store";


export function useReloading() {

    const reload = writable(10);

    onMount(() => {
        const interval = setInterval(() => {
            reload.update(n => {
                const temp = Math.max(0, n - 1);
                if (temp === 0) {
                    location.reload();
                }
                return temp;
            });
        }, 1000);

        return () => {
            clearInterval(interval);
        }
    })

    return reload;

}