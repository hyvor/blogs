import { test, expect } from "vitest";
import { getLinkType } from "./links";

test("get link type", () => {
  const baseUrl = "https://example.com";

  expect(getLinkType("mailto:test@hyvor.com", baseUrl)).toBe("mail");
  expect(getLinkType("tel:123456789", baseUrl)).toBe("tel");
  expect(getLinkType("file:///home/user/file.txt", baseUrl)).toBe("other");
  expect(getLinkType("#anchor", baseUrl)).toBe("anchor");

  // internal-blog
  expect(getLinkType("", baseUrl)).toBe("internal-blog");
  expect(getLinkType("/", baseUrl)).toBe("internal-blog");
  expect(getLinkType("/about", baseUrl)).toBe("internal-blog");
  expect(getLinkType("about", baseUrl)).toBe("internal-blog");
  expect(getLinkType("/blog/other", baseUrl + "/blog")).toBe("internal-blog");

  // internal-domain
  expect(getLinkType("/", baseUrl + "/blog")).toBe("internal-domain");
  expect(getLinkType("/about", baseUrl + "/blog")).toBe("internal-domain");
  expect(getLinkType("https://example.com/about", baseUrl + "/blog")).toBe(
    "internal-domain",
  );
  expect(getLinkType("https://example.com", baseUrl + "/blog")).toBe(
    "internal-domain",
  );

  // internal-root-domain
  expect(getLinkType("https://example.com", "https://blog.example.com")).toBe(
    "internal-root-domain",
  );
  expect(
    getLinkType("https://example.com", "https://blog.example.com/blog"),
  ).toBe("internal-root-domain");
  expect(
    getLinkType("https://example.com/about", "https://blog.example.com"),
  ).toBe("internal-root-domain");
  expect(
    getLinkType(
      "https://subdomain.example.com",
      "https://blog.example.com/blog",
    ),
  ).toBe("internal-root-domain");

  // external
  expect(getLinkType("https://example.com", "https://blog.hyvor.com")).toBe(
    "external",
  );
  expect(
    getLinkType("https://example.com", "https://blog.hyvor.com/blog"),
  ).toBe("external");
  expect(
    getLinkType("https://example.com/about", "https://blog.hyvor.com"),
  ).toBe("external");
  expect(
    getLinkType("https://subdomain.example.com", "https://blog.hyvor.com/blog"),
  ).toBe("external");

  // other
  expect(getLinkType('javascript:alert("hello")', baseUrl)).toBe("other");
  expect(getLinkType("ftp://ftp.example.com/file.zip", baseUrl)).toBe("other");
  expect(getLinkType("data:image/png;base64,iVBORw0KG", baseUrl)).toBe("other");

  // bug #438
  expect(
    getLinkType(
      'https://killedbygoogle.com "https://killedbygoogle.com"',
      baseUrl,
    ),
  ).toBe("other");
});
