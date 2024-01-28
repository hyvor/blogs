# Designing Themes: Tutorial

This is a step-by-step tutorial on how to design a theme in Hyvor Blogs. We wrote this while developing the Default HB theme. Before getting started, make sure you have read the [overview](themes-overview).

## Step 0: Create a development blog

At HB, themes are developed online. This is a different experience for those who usually work offline on a local filesystem.

To start theme development, create a development blog at <a href="/console/new/dev" target="_blank">/console/new/dev</a>. You will get a subdomain that starts with `dev-`. We recommend you to use `dev-{your_theme_name}` so that you will know what this blog was created. Development blogs are free of charge. 5 different IP addresses can visit dev blogs each day, and caching is disabled. They come with some dummy data to make testing easier.

Now, you can write your Twig/SCSS code in the Theme section of the console. To test changes, visit the online URL (`dev-{your_theme_name}.hyvorblogs.io`).

## Step 1: 