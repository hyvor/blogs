export function addDefaultDefs(defs: object) {
  return {
    ...defs,
    ...{
      THEME_NAME: {
        $name: "Theme Name",
        $type: "none",
      },
      THEME_VERSION: {
        $name: "Theme Version",
        $type: "none",
      },
      THEME_FONTS: {
        $name: "Theme Fonts",
        $type: "text",
        $description:
          'Load Google fonts locally. See <a href="https://blogs.hyvor.com/docs/fonts" target="_blank">docs</a> for more info.',
      },
      POSTS_PER_PAGINATION: {
        $name: "Posts per Pagination",
        $description: "Number of posts to show per page on index pages",
        $type: "number",
      },
    },
  };
}

export function getInputType(def: Record<string, any>) {
  const type = def?.$type || "text";

  const types = [
    "none",
    "text",
    "textarea",
    "number",
    "checkbox",
    "radio",
    "select",
    "color",
  ];

  if (types.includes(type)) {
    return type;
  }

  return "text";
}
