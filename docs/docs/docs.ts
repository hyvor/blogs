/**
 * Navigation of the Hyvor Blogs docs (hyvor.com/blogs/docs).
 *
 * This directory is synced to hyvor/core, which renders the docs.
 * Keep it self-contained: only import from within this directory,
 * `svelte`, and `@hyvor/design`.
 */
import type { NavSectionConfig } from "@hyvor/design/marketing";
import type { Component } from "svelte";
import en from "./locale/en.json";
import fr from "./locale/fr.json";

export const LANGUAGES = ["en", "fr"];

const STRINGS: Record<string, typeof en> = { en, fr };

const PAGES = import.meta.glob<{ default: Component }>("./*/*.md");

// loads a page in the given language, falling back to English if it is not translated yet
async function loadPage(lang: string, file: string): Promise<Component> {
  const loader = PAGES[`./${lang}/${file}.md`] ?? PAGES[`./en/${file}.md`];
  if (!loader) {
    throw new Error(`Docs page not found: ${file}`);
  }
  return (await loader()).default;
}

export async function getSections(lang: string): Promise<NavSectionConfig[]> {
  const s = STRINGS[lang] ?? en;
  const getComponent = (file: string) => loadPage(lang, file);

  return [
    {
      name: "",
      navs: [
        {
          type: "page",
          slug: "",
          name: s.pages.introduction,
          description: s.descriptions.introduction,
          content: await getComponent("Introduction"),
        },

        {
          type: "sub-section",
          name: s.sections.writing,
          sections: [
            {
              name: "",
              navs: [
                {
                  type: "page",
                  slug: "writing",
                  name: s.pages.writing,
                  description: s.descriptions.writing,
                  content: await getComponent("Writing"),
                },
                {
                  type: "page",
                  slug: "editor",
                  name: s.pages.editor,
                  description: s.descriptions.editor,
                  content: await getComponent("Editor"),
                },
                {
                  type: "page",
                  slug: "suggestion-mode",
                  name: s.pages.suggestionMode,
                  description: s.descriptions.suggestionMode,
                  content: await getComponent("SuggestionMode"),
                },
              ],
            },
            {
              name: s.sections.postHealth,
              navs: [
                {
                  type: "page",
                  slug: "seo-analyzer",
                  name: s.pages.seoAnalyzer,
                  description: s.descriptions.seoAnalyzer,
                  content: await getComponent("SeoAnalyzer"),
                },
                {
                  type: "page",
                  slug: "link-analyzer",
                  name: s.pages.linkAnalyzer,
                  description: s.descriptions.linkAnalyzer,
                  content: await getComponent("LinkAnalyzer"),
                },
              ],
            },
          ],
        },

        {
          type: "sub-section",
          name: s.pages.themes,
          sections: [
            {
              name: "",
              navs: [
                {
                  type: "page",
                  slug: "themes",
                  name: s.pages.themes,
                  description: s.descriptions.themes,
                  content: await getComponent("Themes"),
                },
              ],
            },
            {
              name: s.sections.themeDevelopment,
              navs: [
                {
                  type: "page",
                  slug: "themes-overview",
                  name: s.pages.themesOverview,
                  description: s.descriptions.themesOverview,
                  content: await getComponent("ThemesOverview"),
                },

                {
                  type: "page",
                  slug: "themes-templates",
                  name: s.pages.themesTemplates,
                  description: s.descriptions.themesTemplates,
                  content: await getComponent("ThemesTemplates"),
                },

                {
                  type: "page",
                  slug: "themes-styles",
                  name: s.pages.themesStyling,
                  description: s.descriptions.themesStyling,
                  content: await getComponent("ThemesStyles"),
                },

                {
                  type: "page",
                  slug: "themes-scripts",
                  name: s.pages.themesScripts,
                  description: s.descriptions.themesScripts,
                  content: await getComponent("ThemeScripts"),
                },

                {
                  type: "page",
                  slug: "themes-internationalization",
                  name: s.pages.themesInternationalization,
                  description: s.descriptions.themesInternationalization,
                  content: await getComponent("ThemesInternationalization"),
                },

                {
                  type: "page",
                  slug: "themes-config",
                  name: s.pages.themesConfig,
                  description: s.descriptions.themesConfig,
                  content: await getComponent("ThemesConfiguration"),
                },

                {
                  type: "page",
                  slug: "themes-publishing",
                  name: s.pages.themesPublishing,
                  description: s.descriptions.themesPublishing,
                  content: await getComponent("ThemesPublishing"),
                },
              ],
            },
          ],
        },

        {
          type: "page",
          slug: "agent",
          name: s.pages.agent,
          description: s.descriptions.agent,
          content: await getComponent("AIAgent"),
        },
      ],
    },

    {
      name: s.sections.hosting,
      navs: [
        {
          type: "page",
          slug: "custom-domain",
          name: s.pages.customDomain,
          description: s.descriptions.customDomain,
          content: await getComponent("CustomDomain"),
        },
        {
          type: "page",
          slug: "subdirectory",
          name: s.pages.subdirectory,
          description: s.descriptions.subdirectory,
          content: await getComponent("SubDirectoryHosting"),
        },
        {
          type: "page",
          slug: "headless",
          name: s.pages.headless,
          description: s.descriptions.headless,
          content: await getComponent("Headless"),
        },
      ],
    },

    {
      name: s.sections.integrations,
      navs: [
        {
          type: "page",
          slug: "hyvor-talk",
          name: s.pages.hyvorTalk,
          description: s.descriptions.hyvorTalk,
          content: await getComponent("HyvorTalk"),
        },
        {
          type: "page",
          slug: "hyvor-post",
          name: s.pages.hyvorPost,
          description: s.descriptions.hyvorPost,
          content: await getComponent("HyvorPost"),
        },
      ],
    },

    {
      name: s.sections.features,
      navs: [
        {
          type: "page",
          slug: "languages",
          name: s.pages.languages,
          description: s.descriptions.languages,
          content: await getComponent("Languages"),
        },
        {
          type: "page",
          slug: "users",
          name: s.pages.users,
          description: s.descriptions.users,
          content: await getComponent("Users"),
        },
        {
          type: "page",
          slug: "tags",
          name: s.pages.tags,
          description: s.descriptions.tags,
          content: await getComponent("Tags"),
        },
        {
          type: "page",
          slug: "media",
          name: s.pages.media,
          description: s.descriptions.media,
          content: await getComponent("Media"),
        },
        {
          type: "page",
          slug: "fonts",
          name: s.pages.fonts,
          description: s.descriptions.fonts,
          content: await getComponent("Fonts"),
        },
        {
          type: "page",
          slug: "seo",
          name: s.pages.seo,
          description: s.descriptions.seo,
          content: await getComponent("Seo"),
        },
        {
          type: "page",
          slug: "navigation-links",
          name: s.pages.navigationLinks,
          description: s.descriptions.navigationLinks,
          content: await getComponent("NavigationLinks"),
        },
        {
          type: "page",
          slug: "redirects",
          name: s.pages.redirects,
          description: s.descriptions.redirects,
          content: await getComponent("Redirect"),
        },
        {
          type: "page",
          slug: "custom-code",
          name: s.pages.customCode,
          description: s.descriptions.customCode,
          content: await getComponent("CustomCode"),
        },
        {
          type: "page",
          slug: "routes",
          name: s.pages.routes,
          description: s.descriptions.routes,
          content: await getComponent("Routes"),
        },
        {
          type: "page",
          slug: "syntax-highlighting",
          name: s.pages.syntaxHighlighting,
          description: s.descriptions.syntaxHighlighting,
          content: await getComponent("SyntaxHighlighting"),
        },
      ],
    },

    {
      name: s.sections.developer,
      navs: [
        {
          type: "page",
          slug: "webhooks",
          name: s.pages.webhooks,
          description: s.descriptions.webhooks,
          content: await getComponent("Webhooks"),
        },
        {
          type: "page",
          slug: "api-console",
          name: s.pages.apiConsole,
          description: s.descriptions.apiConsole,
          content: await getComponent("ApiConsole"),
        },
        {
          type: "page",
          slug: "api-delivery",
          name: s.pages.apiDelivery,
          description: s.descriptions.apiDelivery,
          content: await getComponent("ApiDelivery"),
        },
        {
          type: "page",
          slug: "api-data",
          name: s.pages.apiData,
          description: s.descriptions.apiData,
          content: await getComponent("ApiData"),
        },
      ],
    },

    {
      name: s.sections.data,
      navs: [
        {
          type: "page",
          slug: "export",
          name: s.pages.export,
          description: s.descriptions.export,
          content: await getComponent("Export"),
        },
        {
          type: "folding-section",
          name: s.pages.importData,
          navs: [
            {
              type: "page",
              slug: "import",
              name: s.pages.importOverview,
              description: s.descriptions.importOverview,
              content: await getComponent("Import"),
            },
            {
              type: "page",
              slug: "import-sitemap",
              name: s.pages.importSitemap,
              description: s.descriptions.importSitemap,
              content: await getComponent("ImportSitemap"),
            },
            {
              type: "page",
              slug: "import-wordpress",
              name: s.pages.importWordpress,
              description: s.descriptions.importWordpress,
              content: await getComponent("ImportWordPress"),
            },
          ],
        },
      ],
    },
  ];
}
