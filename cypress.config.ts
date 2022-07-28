import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    baseUrl: "http://blogs.hyvor.test:8080",
  },

  component: {
    devServer: {
      framework: "react",
      bundler: "vite",
    },
  },
});
