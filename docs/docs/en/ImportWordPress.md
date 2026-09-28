# Import from WordPress

Currently, WordPress importing is only available through support. Please contact us at <a href="mailto:blogs.support@hyvor.com">blogs.support@hyvor.com</a> to import your WordPress blog.

Follow the steps below to export your WordPress site:

- Export your posts at **WordPress admin &rarr; Tools &rarr; Export &rarr; All Content &rarr; Download Export File**
- Download your `wp-content/uploads` folder from your WordPress site. This folder contains all the images and other media files used in your posts. You may need to use an FTP client or ask your hosting provider to download this folder.

Once you have the files, create a ZIP file with the following structure:

```
wordpress-export.zip
    ├── export.xml
    └── uploads
        └── 2024
            ├── 01
            │   ├── image1.jpg
            │   └── image2.jpg
            └── 02
                └── image3.jpg
```

Send us the ZIP file and we will handle the rest.
