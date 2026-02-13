--
-- PostgreSQL database dump
--


-- Dumped from database version 16.6 (Debian 16.6-1.pgdg120+1)
-- Dumped by pg_dump version 17.7 (Debian 17.7-0+deb13u1)

--
-- Name: citext; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: blog_hosting_at; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.blog_hosting_at AS ENUM (
    'subdomain',
    'domain',
    'self'
);


--
-- Name: blog_type; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.blog_type AS ENUM (
    'default',
    'dev',
    'preview',
    'temp'
);


--
-- Name: link_analyzer_link_check_types; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.link_analyzer_link_check_types AS ENUM (
    'internal',
    'external'
);


--
-- Name: post_variant_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.post_variant_status AS ENUM (
    'published',
    'draft',
    'scheduled'
);


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: api_keys; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.api_keys (
                                 id bigint NOT NULL,
                                 created_at timestamp(0) without time zone,
                                 updated_at timestamp(0) without time zone,
                                 blog_id bigint NOT NULL,
                                 name character varying(255) NOT NULL,
                                 type character varying(255) NOT NULL,
                                 api_key character varying(32) NOT NULL,
                                 CONSTRAINT api_keys_type_check CHECK (((type)::text = ANY ((ARRAY['console'::character varying, 'delivery'::character varying])::text[])))
);


--
-- Name: api_keys_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.api_keys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: api_keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.api_keys_id_seq OWNED BY public.api_keys.id;


--
-- Name: appsumo_codes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.appsumo_codes (
                                      id bigint NOT NULL,
                                      created_at timestamp(0) without time zone,
                                      updated_at timestamp(0) without time zone,
                                      code character varying(255) NOT NULL,
                                      blog_id bigint,
                                      redeemed_at timestamp(0) without time zone
);


--
-- Name: appsumo_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.appsumo_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: appsumo_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.appsumo_codes_id_seq OWNED BY public.appsumo_codes.id;


--
-- Name: auto_translations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.auto_translations (
                                          id bigint NOT NULL,
                                          created_at timestamp(0) without time zone,
                                          updated_at timestamp(0) without time zone,
                                          blog_id bigint NOT NULL,
                                          source_lang character varying(10) NOT NULL,
                                          target_lang character varying(10) NOT NULL,
                                          chars integer NOT NULL
);


--
-- Name: auto_translations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.auto_translations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: auto_translations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.auto_translations_id_seq OWNED BY public.auto_translations.id;


--
-- Name: blocked_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.blocked_users (
                                      id bigint NOT NULL,
                                      created_at timestamp(0) without time zone,
                                      updated_at timestamp(0) without time zone,
                                      hyvor_user_id bigint NOT NULL
);


--
-- Name: blocked_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blocked_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: blocked_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.blocked_users_id_seq OWNED BY public.blocked_users.id;


--
-- Name: blog_variants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.blog_variants (
                                      id bigint NOT NULL,
                                      blog_id bigint NOT NULL,
                                      language_id bigint NOT NULL,
                                      name character varying(160),
                                      description character varying(255)
);


--
-- Name: blog_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blog_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: blog_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.blog_variants_id_seq OWNED BY public.blog_variants.id;


--
-- Name: blogs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.blogs (
                              id integer NOT NULL,
                              created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                              updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                              ip inet,
                              is_blocked boolean DEFAULT false NOT NULL,
                              blocked_at timestamp without time zone,
                              hyvor_user_id bigint,
                              theme_version_id bigint,
                              subdomain public.citext NOT NULL,
                              trial_ends_at timestamp without time zone NOT NULL,
                              type public.blog_type DEFAULT 'default'::public.blog_type,
                              hosting_at public.blog_hosting_at DEFAULT 'subdomain'::public.blog_hosting_at NOT NULL,
                              hosting_domain public.citext,
                              hosting_url character varying(255),
                              hosting_redirect_subdomain boolean DEFAULT true,
                              meta jsonb,
                              counts jsonb
);


--
-- Name: blogs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blogs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: blogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.blogs_id_seq OWNED BY public.blogs.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
                              key text NOT NULL,
                              value text NOT NULL,
                              expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
                                    key character varying(255) NOT NULL,
                                    owner character varying(255) NOT NULL,
                                    expiration integer NOT NULL
);


--
-- Name: exports; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.exports (
                                id bigint NOT NULL,
                                created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                blog_id bigint NOT NULL,
                                format character varying(255) DEFAULT 'hyvor_blogs'::character varying NOT NULL,
                                status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
                                url character varying(255),
                                error character varying(255),
                                CONSTRAINT exports_format_check CHECK (((format)::text = ANY ((ARRAY['hyvor_blogs'::character varying, 'wordpress'::character varying])::text[]))),
    CONSTRAINT exports_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'failed'::character varying])::text[])))
);


--
-- Name: exports_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.exports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: exports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.exports_id_seq OWNED BY public.exports.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
                                    id bigint NOT NULL,
                                    uuid character varying(255) NOT NULL,
                                    connection text NOT NULL,
                                    queue text NOT NULL,
                                    payload text NOT NULL,
                                    exception text NOT NULL,
                                    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: gpt_prompts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gpt_prompts (
                                    id bigint NOT NULL,
                                    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                    deleted_at timestamp(0) without time zone,
                                    blog_id integer NOT NULL,
                                    post_id integer,
                                    prompt character varying(1000) NOT NULL,
                                    gpt_response text,
                                    model_name character varying(255),
                                    tokens_prompt integer,
                                    tokens_response integer,
                                    tokens_total integer
);


--
-- Name: gpt_prompts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.gpt_prompts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: gpt_prompts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.gpt_prompts_id_seq OWNED BY public.gpt_prompts.id;


--
-- Name: hyvor_talk_gated_content_rules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.hyvor_talk_gated_content_rules (
                                                       id bigint NOT NULL,
                                                       created_at timestamp(0) without time zone,
                                                       updated_at timestamp(0) without time zone,
                                                       blog_id bigint NOT NULL,
                                                       tag_id bigint NOT NULL,
                                                       minimum_plan character varying(255) NOT NULL,
                                                       gate text
);


--
-- Name: hyvor_talk_gated_content_rules_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.hyvor_talk_gated_content_rules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: hyvor_talk_gated_content_rules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.hyvor_talk_gated_content_rules_id_seq OWNED BY public.hyvor_talk_gated_content_rules.id;


--
-- Name: imports; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.imports (
                                id bigint NOT NULL,
                                created_at timestamp(0) without time zone,
                                updated_at timestamp(0) without time zone,
                                blog_id bigint NOT NULL,
                                name character varying(255) NOT NULL,
                                type character varying(255) NOT NULL,
                                status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
                                error character varying(255),
                                options json,
                                posts_count integer DEFAULT 0 NOT NULL,
                                pages_count integer DEFAULT 0 NOT NULL,
                                tags_count integer DEFAULT 0 NOT NULL,
                                users_count integer DEFAULT 0 NOT NULL,
                                CONSTRAINT imports_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'failed'::character varying])::text[]))),
    CONSTRAINT imports_type_check CHECK (((type)::text = ANY ((ARRAY['sitemap'::character varying, 'wordpress'::character varying])::text[])))
);


--
-- Name: imports_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.imports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: imports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.imports_id_seq OWNED BY public.imports.id;


--
-- Name: inter_hyvor_talk_websites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inter_hyvor_talk_websites (
                                                  id bigint NOT NULL,
                                                  created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                                  updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                                  blog_id bigint NOT NULL,
                                                  website_id bigint NOT NULL,
                                                  encryption_key character varying(255)
);


--
-- Name: inter_hyvor_talk_websites_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inter_hyvor_talk_websites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inter_hyvor_talk_websites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inter_hyvor_talk_websites_id_seq OWNED BY public.inter_hyvor_talk_websites.id;


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
                             id bigint NOT NULL,
                             queue character varying(255) NOT NULL,
                             payload text NOT NULL,
                             attempts smallint NOT NULL,
                             reserved_at integer,
                             available_at integer NOT NULL,
                             created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: languages; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.languages (
                                  id bigint NOT NULL,
                                  created_at timestamp(0) without time zone,
                                  updated_at timestamp(0) without time zone,
                                  blog_id bigint NOT NULL,
                                  fallback_language_id bigint,
                                  code character varying(12) NOT NULL,
                                  name character varying(255) NOT NULL,
                                  is_primary boolean DEFAULT false NOT NULL,
                                  direction character varying(255) DEFAULT 'ltr'::character varying NOT NULL,
                                  CONSTRAINT languages_direction_check CHECK (((direction)::text = ANY ((ARRAY['ltr'::character varying, 'rtl'::character varying])::text[])))
);


--
-- Name: languages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.languages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: languages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.languages_id_seq OWNED BY public.languages.id;


--
-- Name: link_analyzer_checks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.link_analyzer_checks (
                                             id bigint NOT NULL,
                                             created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                             updated_at timestamp(0) without time zone,
                                             blog_id bigint NOT NULL,
                                             status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
                                             error character varying(255),
                                             posts_count integer DEFAULT 0 NOT NULL,
                                             post_variants_count integer DEFAULT 0 NOT NULL,
                                             pages_count integer DEFAULT 0 NOT NULL,
                                             page_variants_count integer DEFAULT 0 NOT NULL,
                                             links_total_count integer DEFAULT 0 NOT NULL,
                                             links_ok_count integer DEFAULT 0 NOT NULL,
                                             links_broken_count integer DEFAULT 0 NOT NULL,
                                             links_redirect_count integer DEFAULT 0 NOT NULL,
                                             links_ignored_count integer DEFAULT 0 NOT NULL,
                                             links_risky_count integer DEFAULT 0,
                                             CONSTRAINT link_analyzer_checks_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'failed'::character varying])::text[])))
);


--
-- Name: link_analyzer_checks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.link_analyzer_checks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: link_analyzer_checks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.link_analyzer_checks_id_seq OWNED BY public.link_analyzer_checks.id;


--
-- Name: link_analyzer_links; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.link_analyzer_links (
                                            id bigint NOT NULL,
                                            created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                            updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                            last_checked_at timestamp(0) without time zone NOT NULL,
                                            blog_id integer NOT NULL,
                                            post_variant_id integer NOT NULL,
                                            url character varying(255) NOT NULL,
                                            full_url character varying(255) NOT NULL,
                                            status_code smallint NOT NULL,
                                            ignore boolean DEFAULT false NOT NULL,
                                            check_type public.link_analyzer_link_check_types DEFAULT 'internal'::public.link_analyzer_link_check_types NOT NULL,
                                            ignore_reason character varying(255),
                                            comment text
);


--
-- Name: link_analyzer_links_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.link_analyzer_links_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: link_analyzer_links_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.link_analyzer_links_id_seq OWNED BY public.link_analyzer_links.id;


--
-- Name: media; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.media (
                              id bigint NOT NULL,
                              created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                              updated_at timestamp(0) without time zone,
                              blog_id bigint NOT NULL,
                              post_id bigint,
                              name character varying(255) NOT NULL,
                              size integer DEFAULT 0 NOT NULL,
                              original_name character varying(255) NOT NULL,
                              extension character varying(255)
);


--
-- Name: media_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.media_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: media_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
                                   id integer NOT NULL,
                                   migration character varying(255) NOT NULL,
                                   batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: navigation_variants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.navigation_variants (
                                            id bigint NOT NULL,
                                            created_at timestamp(0) without time zone,
                                            updated_at timestamp(0) without time zone,
                                            navigation_id bigint NOT NULL,
                                            language_id bigint NOT NULL,
                                            name character varying(255)
);


--
-- Name: navigation_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.navigation_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: navigation_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.navigation_variants_id_seq OWNED BY public.navigation_variants.id;


--
-- Name: navigations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.navigations (
                                    id bigint NOT NULL,
                                    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                    blog_id bigint NOT NULL,
                                    url character varying(255) NOT NULL,
                                    type character varying(255) NOT NULL,
                                    sort integer DEFAULT 0 NOT NULL,
                                    CONSTRAINT navigations_type_check CHECK (((type)::text = ANY ((ARRAY['header'::character varying, 'footer'::character varying])::text[])))
);


--
-- Name: navigations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.navigations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: navigations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.navigations_id_seq OWNED BY public.navigations.id;


--
-- Name: post_author; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.post_author (
                                    id bigint NOT NULL,
                                    created_at timestamp(0) without time zone,
                                    updated_at timestamp(0) without time zone,
                                    post_id bigint NOT NULL,
                                    user_id bigint NOT NULL
);


--
-- Name: post_author_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.post_author_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: post_author_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.post_author_id_seq OWNED BY public.post_author.id;


--
-- Name: post_tag; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.post_tag (
                                 id bigint NOT NULL,
                                 created_at timestamp(0) without time zone,
                                 updated_at timestamp(0) without time zone,
                                 post_id bigint NOT NULL,
                                 tag_id bigint NOT NULL
);


--
-- Name: post_tag_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.post_tag_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: post_tag_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.post_tag_id_seq OWNED BY public.post_tag.id;


--
-- Name: post_variant_histories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.post_variant_histories (
                                               id bigint NOT NULL,
                                               created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                               updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                               post_variant_id bigint NOT NULL,
                                               content text NOT NULL
);


--
-- Name: post_variant_histories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.post_variant_histories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: post_variant_histories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.post_variant_histories_id_seq OWNED BY public.post_variant_histories.id;


--
-- Name: post_variants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.post_variants (
                                      id integer NOT NULL,
                                      created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                                      updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                                      post_id bigint NOT NULL,
                                      language_id bigint NOT NULL,
                                      slug public.citext,
                                      status public.post_variant_status DEFAULT 'draft'::public.post_variant_status NOT NULL,
                                      content text,
                                      content_unsaved text,
                                      content_html text,
                                      content_text text,
                                      title character varying(255),
                                      description character varying(350),
                                      words integer,
                                      seo_primary_keyword character varying(255),
                                      seo_secondary_keywords jsonb,
                                      link_analysis jsonb,
                                      ts_language regconfig DEFAULT 'simple'::regconfig,
                                      ts tsvector GENERATED ALWAYS AS (to_tsvector(ts_language, (((((((COALESCE(title, ''::character varying))::text || ' '::text) || (COALESCE(description, ''::character varying))::text) || ' '::text) || (COALESCE(slug, ''::public.citext))::text) || ' '::text) || COALESCE(content_text, ''::text)))) STORED,
                                      calculated_ts tsvector GENERATED ALWAYS AS ((((setweight(to_tsvector(ts_language, (COALESCE(title, ''::character varying))::text), 'A'::"char") || setweight(to_tsvector(ts_language, (COALESCE(slug, ''::public.citext))::text), 'B'::"char")) || setweight(to_tsvector(ts_language, (COALESCE(description, ''::character varying))::text), 'C'::"char")) || setweight(to_tsvector(ts_language, COALESCE(content_text, ''::text)), 'D'::"char"))) STORED
);


--
-- Name: post_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.post_variants_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: post_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.post_variants_id_seq OWNED BY public.post_variants.id;


--
-- Name: posts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.posts (
                              id bigint NOT NULL,
                              created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                              updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                              published_at timestamp(0) without time zone,
                              blog_id bigint NOT NULL,
                              is_page boolean DEFAULT false NOT NULL,
                              is_featured boolean DEFAULT false NOT NULL,
                              featured_image_url character varying(255),
                              canonical_url character varying(255),
                              code_head text,
                              code_foot text
);


--
-- Name: posts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.posts_id_seq OWNED BY public.posts.id;


--
-- Name: redirects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.redirects (
                                  id bigint NOT NULL,
                                  created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                  updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                  blog_id bigint NOT NULL,
                                  dynamic boolean DEFAULT false NOT NULL,
                                  path character varying(255) NOT NULL,
                                  "to" character varying(255) NOT NULL,
                                  type character varying(255) NOT NULL,
                                  CONSTRAINT redirects_type_check CHECK (((type)::text = ANY ((ARRAY['permanent'::character varying, 'temporary'::character varying])::text[])))
);


--
-- Name: redirects_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.redirects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: redirects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.redirects_id_seq OWNED BY public.redirects.id;


--
-- Name: routes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.routes (
                               id bigint NOT NULL,
                               created_at timestamp(0) without time zone,
                               updated_at timestamp(0) without time zone,
                               blog_id bigint NOT NULL,
                               name character varying(255) NOT NULL,
                               match character varying(255) NOT NULL,
                               template character varying(255) NOT NULL,
                               posts_filter character varying(255),
                               content_type character varying(255),
                               is_enabled boolean DEFAULT true NOT NULL
);


--
-- Name: routes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.routes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: routes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.routes_id_seq OWNED BY public.routes.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.subscriptions (
                                      id bigint NOT NULL,
                                      created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                      updated_at timestamp(0) without time zone,
                                      blog_id bigint NOT NULL,
                                      status character varying(255) NOT NULL,
                                      plan character varying(255) NOT NULL,
                                      frequency character varying(255) NOT NULL,
                                      ends_at timestamp(0) without time zone,
                                      meta json,
                                      CONSTRAINT subscriptions_frequency_check CHECK (((frequency)::text = ANY ((ARRAY['monthly'::character varying, 'yearly'::character varying])::text[]))),
    CONSTRAINT subscriptions_plan_check CHECK (((plan)::text = ANY ((ARRAY['starter'::character varying, 'growth'::character varying, 'premium'::character varying, 'team'::character varying, 'business'::character varying, 'enterprise'::character varying])::text[]))),
    CONSTRAINT subscriptions_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'past_due'::character varying, 'deleted'::character varying])::text[])))
);


--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.subscriptions_id_seq OWNED BY public.subscriptions.id;


--
-- Name: tag_variants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tag_variants (
                                     id bigint NOT NULL,
                                     created_at timestamp(0) without time zone,
                                     updated_at timestamp(0) without time zone,
                                     tag_id bigint NOT NULL,
                                     language_id bigint NOT NULL,
                                     name character varying(255),
                                     description character varying(255)
);


--
-- Name: tag_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tag_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tag_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tag_variants_id_seq OWNED BY public.tag_variants.id;


--
-- Name: tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tags (
                             id integer NOT NULL,
                             created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                             updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                             blog_id bigint NOT NULL,
                             slug public.citext NOT NULL,
                             posts_count integer DEFAULT 0,
                             code_head text,
                             code_foot text,
                             is_private boolean DEFAULT false
);


--
-- Name: tags_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tags_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tags_id_seq OWNED BY public.tags.id;


--
-- Name: theme_files; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.theme_files (
                                    id bigint NOT NULL,
                                    created_at timestamp(0) without time zone,
                                    updated_at timestamp(0) without time zone,
                                    blog_id bigint NOT NULL,
                                    folder character varying(255),
                                    name character varying(255) NOT NULL,
                                    content bytea,
                                    CONSTRAINT theme_files_folder_check CHECK (((folder)::text = ANY ((ARRAY['templates'::character varying, 'assets'::character varying, 'styles'::character varying, 'lang'::character varying])::text[])))
);


--
-- Name: theme_files_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.theme_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: theme_files_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.theme_files_id_seq OWNED BY public.theme_files.id;


--
-- Name: theme_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.theme_versions (
                                       id bigint NOT NULL,
                                       created_at timestamp(0) without time zone,
                                       updated_at timestamp(0) without time zone,
                                       theme_id bigint NOT NULL,
                                       version character varying(255) NOT NULL,
                                       preview_subdomain character varying(255),
                                       zip bytea
);


--
-- Name: theme_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.theme_versions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: theme_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.theme_versions_id_seq OWNED BY public.theme_versions.id;


--
-- Name: themes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.themes (
                               id bigint NOT NULL,
                               created_at timestamp(0) without time zone,
                               updated_at timestamp(0) without time zone,
                               name character varying(255) NOT NULL,
                               type character varying(255) NOT NULL,
                               blogs_count integer DEFAULT 0 NOT NULL,
                               CONSTRAINT themes_type_check CHECK (((type)::text = ANY ((ARRAY['original'::character varying, 'ported'::character varying])::text[])))
);


--
-- Name: themes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.themes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: themes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.themes_id_seq OWNED BY public.themes.id;


--
-- Name: url_data; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.url_data (
                                 id bigint NOT NULL,
                                 created_at timestamp(0) without time zone,
                                 updated_at timestamp(0) without time zone,
                                 result character varying(255) NOT NULL,
                                 fetch_type character varying(255) NOT NULL,
                                 url character varying(255) NOT NULL,
                                 final_url character varying(255),
                                 html text,
                                 title character varying(255),
                                 description character varying(255),
                                 thumbnail_url character varying(255),
                                 icon_url character varying(255),
                                 site character varying(255),
                                 CONSTRAINT url_data_fetch_type_check CHECK (((fetch_type)::text = ANY ((ARRAY['link'::character varying, 'embed'::character varying])::text[]))),
    CONSTRAINT url_data_result_check CHECK (((result)::text = ANY ((ARRAY['ok'::character varying, 'err'::character varying])::text[])))
);


--
-- Name: url_data_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.url_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: url_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.url_data_id_seq OWNED BY public.url_data.id;


--
-- Name: user_variants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_variants (
                                      id bigint NOT NULL,
                                      updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                      user_id bigint NOT NULL,
                                      language_id bigint NOT NULL,
                                      name character varying(50),
                                      bio character varying(255),
                                      location character varying(50)
);


--
-- Name: user_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_variants_id_seq OWNED BY public.user_variants.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
                              id bigint NOT NULL,
                              created_at timestamp(0) without time zone,
                              updated_at timestamp(0) without time zone,
                              blog_id bigint NOT NULL,
                              hyvor_user_id bigint,
                              role character varying(255) NOT NULL,
                              status character varying(255) DEFAULT 'invited'::character varying NOT NULL,
                              slug character varying(255) NOT NULL,
                              email character varying(255),
                              website_url character varying(255),
                              picture_url character varying(255),
                              social_facebook character varying(255),
                              social_twitter character varying(255),
                              social_linkedin character varying(255),
                              social_youtube character varying(255),
                              social_tiktok character varying(255),
                              social_instagram character varying(255),
                              social_github character varying(255),
                              posts_count integer DEFAULT 0 NOT NULL,
                              sort integer DEFAULT 0 NOT NULL,
                              CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['owner'::character varying, 'admin'::character varying, 'editor'::character varying, 'writer'::character varying, 'contributor'::character varying])::text[]))),
    CONSTRAINT users_status_check CHECK (((status)::text = ANY ((ARRAY['invited'::character varying, 'active'::character varying, 'blocked'::character varying])::text[])))
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: webhook_deliveries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.webhook_deliveries (
                                           id bigint NOT NULL,
                                           created_at timestamp(0) without time zone,
                                           updated_at timestamp(0) without time zone,
                                           webhook_id bigint NOT NULL,
                                           status character varying(255) NOT NULL,
                                           url character varying(255) NOT NULL,
                                           event character varying(255) NOT NULL,
                                           data json NOT NULL,
                                           response character varying(1024),
                                           http_status integer,
                                           CONSTRAINT webhook_deliveries_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'retrying'::character varying, 'failed'::character varying, 'success'::character varying])::text[])))
);


--
-- Name: webhook_deliveries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.webhook_deliveries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: webhook_deliveries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.webhook_deliveries_id_seq OWNED BY public.webhook_deliveries.id;


--
-- Name: webhooks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.webhooks (
                                 id bigint NOT NULL,
                                 created_at timestamp(0) without time zone,
                                 updated_at timestamp(0) without time zone,
                                 blog_id bigint NOT NULL,
                                 url character varying(255) NOT NULL,
                                 events json NOT NULL,
                                 secret character varying(32) NOT NULL
);


--
-- Name: webhooks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.webhooks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: webhooks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.webhooks_id_seq OWNED BY public.webhooks.id;


--
-- Name: api_keys id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys ALTER COLUMN id SET DEFAULT nextval('public.api_keys_id_seq'::regclass);


--
-- Name: appsumo_codes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.appsumo_codes ALTER COLUMN id SET DEFAULT nextval('public.appsumo_codes_id_seq'::regclass);


--
-- Name: auto_translations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auto_translations ALTER COLUMN id SET DEFAULT nextval('public.auto_translations_id_seq'::regclass);


--
-- Name: blocked_users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blocked_users ALTER COLUMN id SET DEFAULT nextval('public.blocked_users_id_seq'::regclass);


--
-- Name: blog_variants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blog_variants ALTER COLUMN id SET DEFAULT nextval('public.blog_variants_id_seq'::regclass);


--
-- Name: blogs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogs ALTER COLUMN id SET DEFAULT nextval('public.blogs_id_seq'::regclass);


--
-- Name: exports id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.exports ALTER COLUMN id SET DEFAULT nextval('public.exports_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: gpt_prompts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gpt_prompts ALTER COLUMN id SET DEFAULT nextval('public.gpt_prompts_id_seq'::regclass);


--
-- Name: hyvor_talk_gated_content_rules id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hyvor_talk_gated_content_rules ALTER COLUMN id SET DEFAULT nextval('public.hyvor_talk_gated_content_rules_id_seq'::regclass);


--
-- Name: imports id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.imports ALTER COLUMN id SET DEFAULT nextval('public.imports_id_seq'::regclass);


--
-- Name: inter_hyvor_talk_websites id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inter_hyvor_talk_websites ALTER COLUMN id SET DEFAULT nextval('public.inter_hyvor_talk_websites_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: languages id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.languages ALTER COLUMN id SET DEFAULT nextval('public.languages_id_seq'::regclass);


--
-- Name: link_analyzer_checks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_analyzer_checks ALTER COLUMN id SET DEFAULT nextval('public.link_analyzer_checks_id_seq'::regclass);


--
-- Name: link_analyzer_links id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_analyzer_links ALTER COLUMN id SET DEFAULT nextval('public.link_analyzer_links_id_seq'::regclass);


--
-- Name: media id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: navigation_variants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.navigation_variants ALTER COLUMN id SET DEFAULT nextval('public.navigation_variants_id_seq'::regclass);


--
-- Name: navigations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.navigations ALTER COLUMN id SET DEFAULT nextval('public.navigations_id_seq'::regclass);


--
-- Name: post_author id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_author ALTER COLUMN id SET DEFAULT nextval('public.post_author_id_seq'::regclass);


--
-- Name: post_tag id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_tag ALTER COLUMN id SET DEFAULT nextval('public.post_tag_id_seq'::regclass);


--
-- Name: post_variant_histories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_variant_histories ALTER COLUMN id SET DEFAULT nextval('public.post_variant_histories_id_seq'::regclass);


--
-- Name: post_variants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_variants ALTER COLUMN id SET DEFAULT nextval('public.post_variants_id_seq'::regclass);


--
-- Name: posts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts ALTER COLUMN id SET DEFAULT nextval('public.posts_id_seq'::regclass);


--
-- Name: redirects id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.redirects ALTER COLUMN id SET DEFAULT nextval('public.redirects_id_seq'::regclass);


--
-- Name: routes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes ALTER COLUMN id SET DEFAULT nextval('public.routes_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subscriptions ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_id_seq'::regclass);


--
-- Name: tag_variants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tag_variants ALTER COLUMN id SET DEFAULT nextval('public.tag_variants_id_seq'::regclass);


--
-- Name: tags id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags ALTER COLUMN id SET DEFAULT nextval('public.tags_id_seq'::regclass);


--
-- Name: theme_files id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.theme_files ALTER COLUMN id SET DEFAULT nextval('public.theme_files_id_seq'::regclass);


--
-- Name: theme_versions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.theme_versions ALTER COLUMN id SET DEFAULT nextval('public.theme_versions_id_seq'::regclass);


--
-- Name: themes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.themes ALTER COLUMN id SET DEFAULT nextval('public.themes_id_seq'::regclass);


--
-- Name: url_data id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.url_data ALTER COLUMN id SET DEFAULT nextval('public.url_data_id_seq'::regclass);


--
-- Name: user_variants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_variants ALTER COLUMN id SET DEFAULT nextval('public.user_variants_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: webhook_deliveries id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhook_deliveries ALTER COLUMN id SET DEFAULT nextval('public.webhook_deliveries_id_seq'::regclass);


--
-- Name: webhooks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhooks ALTER COLUMN id SET DEFAULT nextval('public.webhooks_id_seq'::regclass);


--
-- Name: api_keys api_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.api_keys
    ADD CONSTRAINT api_keys_pkey PRIMARY KEY (id);


--
-- Name: appsumo_codes appsumo_codes_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.appsumo_codes
    ADD CONSTRAINT appsumo_codes_code_unique UNIQUE (code);


--
-- Name: appsumo_codes appsumo_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.appsumo_codes
    ADD CONSTRAINT appsumo_codes_pkey PRIMARY KEY (id);


--
-- Name: auto_translations auto_translations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auto_translations
    ADD CONSTRAINT auto_translations_pkey PRIMARY KEY (id);


--
-- Name: blocked_users blocked_users_hyvor_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blocked_users
    ADD CONSTRAINT blocked_users_hyvor_user_id_unique UNIQUE (hyvor_user_id);


--
-- Name: blocked_users blocked_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blocked_users
    ADD CONSTRAINT blocked_users_pkey PRIMARY KEY (id);


--
-- Name: blog_variants blog_variants_blog_id_language_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blog_variants
    ADD CONSTRAINT blog_variants_blog_id_language_id_unique UNIQUE (blog_id, language_id);


--
-- Name: blog_variants blog_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blog_variants
    ADD CONSTRAINT blog_variants_pkey PRIMARY KEY (id);


--
-- Name: blogs blogs_hosting_domain_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogs
    ADD CONSTRAINT blogs_hosting_domain_key UNIQUE (hosting_domain);


--
-- Name: blogs blogs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogs
    ADD CONSTRAINT blogs_pkey PRIMARY KEY (id);


--
-- Name: blogs blogs_subdomain_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blogs
    ADD CONSTRAINT blogs_subdomain_key UNIQUE (subdomain);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: exports exports_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.exports
    ADD CONSTRAINT exports_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: gpt_prompts gpt_prompts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.gpt_prompts
    ADD CONSTRAINT gpt_prompts_pkey PRIMARY KEY (id);


--
-- Name: hyvor_talk_gated_content_rules hyvor_talk_gated_content_rules_blog_id_tag_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hyvor_talk_gated_content_rules
    ADD CONSTRAINT hyvor_talk_gated_content_rules_blog_id_tag_id_unique UNIQUE (blog_id, tag_id);


--
-- Name: hyvor_talk_gated_content_rules hyvor_talk_gated_content_rules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hyvor_talk_gated_content_rules
    ADD CONSTRAINT hyvor_talk_gated_content_rules_pkey PRIMARY KEY (id);


--
-- Name: imports imports_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.imports
    ADD CONSTRAINT imports_pkey PRIMARY KEY (id);


--
-- Name: inter_hyvor_talk_websites inter_hyvor_talk_websites_blog_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inter_hyvor_talk_websites
    ADD CONSTRAINT inter_hyvor_talk_websites_blog_id_unique UNIQUE (blog_id);


--
-- Name: inter_hyvor_talk_websites inter_hyvor_talk_websites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inter_hyvor_talk_websites
    ADD CONSTRAINT inter_hyvor_talk_websites_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: languages languages_blog_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.languages
    ADD CONSTRAINT languages_blog_id_code_unique UNIQUE (blog_id, code);


--
-- Name: languages languages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.languages
    ADD CONSTRAINT languages_pkey PRIMARY KEY (id);


--
-- Name: link_analyzer_checks link_analyzer_checks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_analyzer_checks
    ADD CONSTRAINT link_analyzer_checks_pkey PRIMARY KEY (id);


--
-- Name: link_analyzer_links link_analyzer_links_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_analyzer_links
    ADD CONSTRAINT link_analyzer_links_pkey PRIMARY KEY (id);


--
-- Name: link_analyzer_links link_analyzer_links_post_variant_id_url_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.link_analyzer_links
    ADD CONSTRAINT link_analyzer_links_post_variant_id_url_unique UNIQUE (post_variant_id, url);


--
-- Name: media media_blog_id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_blog_id_name_unique UNIQUE (blog_id, name);


--
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: navigation_variants navigation_variants_navigation_id_language_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.navigation_variants
    ADD CONSTRAINT navigation_variants_navigation_id_language_id_unique UNIQUE (navigation_id, language_id);


--
-- Name: navigation_variants navigation_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.navigation_variants
    ADD CONSTRAINT navigation_variants_pkey PRIMARY KEY (id);


--
-- Name: navigations navigations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.navigations
    ADD CONSTRAINT navigations_pkey PRIMARY KEY (id);


--
-- Name: post_author post_author_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_author
    ADD CONSTRAINT post_author_pkey PRIMARY KEY (id);


--
-- Name: post_author post_author_post_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_author
    ADD CONSTRAINT post_author_post_id_user_id_unique UNIQUE (post_id, user_id);


--
-- Name: post_tag post_tag_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_tag
    ADD CONSTRAINT post_tag_pkey PRIMARY KEY (id);


--
-- Name: post_tag post_tag_post_id_tag_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_tag
    ADD CONSTRAINT post_tag_post_id_tag_id_unique UNIQUE (post_id, tag_id);


--
-- Name: post_variant_histories post_variant_histories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_variant_histories
    ADD CONSTRAINT post_variant_histories_pkey PRIMARY KEY (id);


--
-- Name: post_variants post_variants_language_id_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_variants
    ADD CONSTRAINT post_variants_language_id_slug_key UNIQUE (language_id, slug);


--
-- Name: post_variants post_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_variants
    ADD CONSTRAINT post_variants_pkey PRIMARY KEY (id);


--
-- Name: post_variants post_variants_post_id_language_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_variants
    ADD CONSTRAINT post_variants_post_id_language_id_key UNIQUE (post_id, language_id);


--
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);


--
-- Name: redirects redirects_blog_id_path_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.redirects
    ADD CONSTRAINT redirects_blog_id_path_unique UNIQUE (blog_id, path);


--
-- Name: redirects redirects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.redirects
    ADD CONSTRAINT redirects_pkey PRIMARY KEY (id);


--
-- Name: routes routes_blog_id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_blog_id_name_unique UNIQUE (blog_id, name);


--
-- Name: routes routes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: tag_variants tag_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tag_variants
    ADD CONSTRAINT tag_variants_pkey PRIMARY KEY (id);


--
-- Name: tag_variants tag_variants_tag_id_language_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tag_variants
    ADD CONSTRAINT tag_variants_tag_id_language_id_unique UNIQUE (tag_id, language_id);


--
-- Name: tags tags_blog_id_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_blog_id_slug_key UNIQUE (blog_id, slug);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: theme_files theme_files_blog_id_folder_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.theme_files
    ADD CONSTRAINT theme_files_blog_id_folder_name_unique UNIQUE (blog_id, folder, name);


--
-- Name: theme_files theme_files_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.theme_files
    ADD CONSTRAINT theme_files_pkey PRIMARY KEY (id);


--
-- Name: theme_versions theme_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.theme_versions
    ADD CONSTRAINT theme_versions_pkey PRIMARY KEY (id);


--
-- Name: theme_versions theme_versions_theme_id_version_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.theme_versions
    ADD CONSTRAINT theme_versions_theme_id_version_unique UNIQUE (theme_id, version);


--
-- Name: themes themes_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.themes
    ADD CONSTRAINT themes_name_unique UNIQUE (name);


--
-- Name: themes themes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.themes
    ADD CONSTRAINT themes_pkey PRIMARY KEY (id);


--
-- Name: url_data url_data_fetch_type_url_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.url_data
    ADD CONSTRAINT url_data_fetch_type_url_unique UNIQUE (fetch_type, url);


--
-- Name: url_data url_data_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.url_data
    ADD CONSTRAINT url_data_pkey PRIMARY KEY (id);


--
-- Name: user_variants user_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_variants
    ADD CONSTRAINT user_variants_pkey PRIMARY KEY (id);


--
-- Name: user_variants user_variants_user_id_language_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_variants
    ADD CONSTRAINT user_variants_user_id_language_id_unique UNIQUE (user_id, language_id);


--
-- Name: users users_blog_id_hyvor_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_blog_id_hyvor_user_id_unique UNIQUE (blog_id, hyvor_user_id);


--
-- Name: users users_blog_id_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_blog_id_slug_unique UNIQUE (blog_id, slug);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: webhook_deliveries webhook_deliveries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhook_deliveries
    ADD CONSTRAINT webhook_deliveries_pkey PRIMARY KEY (id);


--
-- Name: webhooks webhooks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhooks
    ADD CONSTRAINT webhooks_pkey PRIMARY KEY (id);


--
-- Name: auto_translations_blog_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX auto_translations_blog_id_created_at_index ON public.auto_translations USING btree (blog_id, created_at);


--
-- Name: auto_translations_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX auto_translations_blog_id_index ON public.auto_translations USING btree (blog_id);


--
-- Name: blog_variants_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blog_variants_blog_id_index ON public.blog_variants USING btree (blog_id);


--
-- Name: blog_variants_language_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blog_variants_language_id_index ON public.blog_variants USING btree (language_id);


--
-- Name: calculated_ts_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX calculated_ts_idx ON public.post_variants USING gin (calculated_ts);


--
-- Name: gpt_prompts_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gpt_prompts_blog_id_index ON public.gpt_prompts USING btree (blog_id);


--
-- Name: gpt_prompts_post_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX gpt_prompts_post_id_index ON public.gpt_prompts USING btree (post_id);


--
-- Name: hyvor_talk_gated_content_rules_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX hyvor_talk_gated_content_rules_blog_id_index ON public.hyvor_talk_gated_content_rules USING btree (blog_id);


--
-- Name: imports_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX imports_blog_id_index ON public.imports USING btree (blog_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: link_analyzer_links_post_variant_id_last_checked_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX link_analyzer_links_post_variant_id_last_checked_at_index ON public.link_analyzer_links USING btree (post_variant_id, last_checked_at);


--
-- Name: link_analyzer_links_url_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX link_analyzer_links_url_index ON public.link_analyzer_links USING btree (url);


--
-- Name: media_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX media_blog_id_index ON public.media USING btree (blog_id);


--
-- Name: media_extension_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX media_extension_index ON public.media USING btree (extension);


--
-- Name: navigation_variants_language_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX navigation_variants_language_id_index ON public.navigation_variants USING btree (language_id);


--
-- Name: navigation_variants_navigation_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX navigation_variants_navigation_id_index ON public.navigation_variants USING btree (navigation_id);


--
-- Name: navigations_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX navigations_blog_id_index ON public.navigations USING btree (blog_id);


--
-- Name: post_author_post_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_author_post_id_index ON public.post_author USING btree (post_id);


--
-- Name: post_author_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_author_user_id_index ON public.post_author USING btree (user_id);


--
-- Name: post_tag_post_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_tag_post_id_index ON public.post_tag USING btree (post_id);


--
-- Name: post_tag_tag_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_tag_tag_id_index ON public.post_tag USING btree (tag_id);


--
-- Name: post_variants_language_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_variants_language_id_index ON public.post_variants USING btree (language_id);


--
-- Name: post_variants_post_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_variants_post_id_index ON public.post_variants USING btree (post_id);


--
-- Name: post_variants_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_variants_status_index ON public.post_variants USING btree (status);


--
-- Name: post_variants_words_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX post_variants_words_index ON public.post_variants USING btree (words);


--
-- Name: posts_blog_id_canonical_url_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_canonical_url_index ON public.posts USING btree (blog_id, canonical_url);


--
-- Name: posts_blog_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_created_at_index ON public.posts USING btree (blog_id, created_at);


--
-- Name: posts_blog_id_featured_image_url_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_featured_image_url_index ON public.posts USING btree (blog_id, featured_image_url);


--
-- Name: posts_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_index ON public.posts USING btree (blog_id);


--
-- Name: posts_blog_id_is_featured_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_is_featured_index ON public.posts USING btree (blog_id, is_featured);


--
-- Name: posts_blog_id_is_page_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_is_page_index ON public.posts USING btree (blog_id, is_page);


--
-- Name: posts_blog_id_published_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_published_at_index ON public.posts USING btree (blog_id, published_at);


--
-- Name: posts_blog_id_updated_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX posts_blog_id_updated_at_index ON public.posts USING btree (blog_id, updated_at);


--
-- Name: redirects_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX redirects_blog_id_index ON public.redirects USING btree (blog_id);


--
-- Name: tag_variants_language_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tag_variants_language_id_index ON public.tag_variants USING btree (language_id);


--
-- Name: tag_variants_tag_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tag_variants_tag_id_index ON public.tag_variants USING btree (tag_id);


--
-- Name: tags_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX tags_blog_id_index ON public.tags USING btree (blog_id);


--
-- Name: ts_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ts_idx ON public.post_variants USING gin (ts);


--
-- Name: user_variants_language_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_variants_language_id_index ON public.user_variants USING btree (language_id);


--
-- Name: user_variants_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_variants_name_index ON public.user_variants USING btree (name);


--
-- Name: user_variants_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_variants_user_id_index ON public.user_variants USING btree (user_id);


--
-- Name: users_blog_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_blog_id_created_at_index ON public.users USING btree (blog_id, created_at);


--
-- Name: users_blog_id_posts_count_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_blog_id_posts_count_index ON public.users USING btree (blog_id, posts_count);


--
-- Name: webhooks_blog_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX webhooks_blog_id_index ON public.webhooks USING btree (blog_id);


--
-- Name: hyvor_talk_gated_content_rules hyvor_talk_gated_content_rules_blog_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hyvor_talk_gated_content_rules
    ADD CONSTRAINT hyvor_talk_gated_content_rules_blog_id_foreign FOREIGN KEY (blog_id) REFERENCES public.blogs(id) ON DELETE CASCADE;


--
-- Name: hyvor_talk_gated_content_rules hyvor_talk_gated_content_rules_tag_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.hyvor_talk_gated_content_rules
    ADD CONSTRAINT hyvor_talk_gated_content_rules_tag_id_foreign FOREIGN KEY (tag_id) REFERENCES public.tags(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--


--
-- PostgreSQL database dump
--


-- Dumped from database version 16.6 (Debian 16.6-1.pgdg120+1)
-- Dumped by pg_dump version 17.7 (Debian 17.7-0+deb13u1)

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

-- SELECT pg_catalog.setval('public.migrations_id_seq', 42, true);


--
-- PostgreSQL database dump complete
--


