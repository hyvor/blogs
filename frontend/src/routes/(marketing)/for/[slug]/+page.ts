import { error } from "@sveltejs/kit";
import { platforms } from "./platforms";

export async function load({ params }) {
  const slug = params.slug;
  const platform = platforms.find((p) => p.slug === slug);

  if (!platform) {
    error(404, "Not found");
  }

  return platform;
}
