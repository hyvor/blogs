<script>
	import { Button, Tooltip } from "@hyvor/design/components";
	import { IconCloudDownload, IconCloudUpload } from "@hyvor/icons";
</script>

<Tooltip
    text="Upload a theme from a zip file"
>

    <Button color="soft" size="small" style="font-size:13px">
        <IconCloudUpload size={16} slot="start" />
        Upload
    </Button>

    <Button color="soft" size="small" style="font-size:13px">
        <IconCloudDownload size={16} slot="start" />
        Download
    </Button>

</Tooltip>