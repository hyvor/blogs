import { beforeNavigate } from "$app/navigation";
import { get } from "svelte/store";
import { themeFilesOriginalStore, themeFilesStore } from "../../lib/stores/themeStore";


export function useIsFileEditingCheck() {

    beforeNavigate(async (navigation) => {

        let hasChanges = false;

        get(themeFilesStore).forEach((file) => {
            const original = get(themeFilesOriginalStore).find((originalFile) => originalFile.id === file.id);
            if (original && original.content !== file.content) hasChanges = true;
        });

        if (hasChanges) {
            // TODO: Upgrade to HDS confirm
            if (!confirm('Are you sure you want to leave this page? Some files are not saved.')) {
                navigation.cancel();
            }
        }
            
    });

}