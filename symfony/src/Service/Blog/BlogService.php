<?php

namespace App\Service\Blog;

use App\Api\Console\Input\Blog\UpdateBlogInput;
use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Entity\Language;
use App\Service\Blog\Event\BlogDeletedEvent;
use App\Service\Blog\Event\BlogUpdatedEvent;
use App\Service\Blog\Event\BlogVariantUpdatedEvent;
use App\Service\CustomDomain\CustomDomainService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BlogService
{
    use ClockAwareTrait;

    public const string SUBDOMAIN_REGEX = '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i';

    // private const FEATURED_IMAGE_URL = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images/post-featured-images';

    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $ed,
        private CustomDomainService $customDomainService,
    ) {}

    public function isSubdomainReserved(string $subdomain): bool
    {
        return in_array($subdomain, ['new', 'billing', 'select'], true);
    }

    public function getBlogById(int $id): ?Blog
    {
        return $this->em->getRepository(Blog::class)->find($id);
    }

    public function getBlogBySubdomain(string $subdomain): ?Blog
    {
        return $this->em->getRepository(Blog::class)->findOneBy(['subdomain' => $subdomain]);
    }

    public function getBlogByCustomDomain(string $customDomain): ?Blog
    {
        return $this->customDomainService->getBlogByCustomDomain($customDomain);
    }

    public function updateBlog(Blog $blog, UpdateBlogInput $input): Blog
    {
        $blogOld = clone $blog;

        if ($input->subdomain !== null) {
            $blog->setSubdomain($input->subdomain);
        }

        if ($input->hosting_redirect_subdomain !== null) {
            $blog->setHostingRedirectSubdomain($input->hosting_redirect_subdomain);
        }

        $meta = clone $blog->getMeta();

        if ($input->embeddable !== null) {
            $meta->embeddable = $input->embeddable;
        }
        if ($input->embedding_domains !== null) {
            $meta->embedding_domains = $input->embedding_domains;
        }
        if ($input->logo_url !== null) {
            $meta->logo_url = $input->logo_url;
        }
        if ($input->icon_url !== null) {
            $meta->icon_url = $input->icon_url;
        }
        if ($input->cover_url !== null) {
            $meta->cover_url = $input->cover_url;
        }
        if ($input->social_facebook !== null) {
            $meta->social_facebook = $input->social_facebook;
        }
        if ($input->social_twitter !== null) {
            $meta->social_twitter = $input->social_twitter;
        }
        if ($input->social_linkedin !== null) {
            $meta->social_linkedin = $input->social_linkedin;
        }
        if ($input->social_youtube !== null) {
            $meta->social_youtube = $input->social_youtube;
        }
        if ($input->social_tiktok !== null) {
            $meta->social_tiktok = $input->social_tiktok;
        }
        if ($input->social_instagram !== null) {
            $meta->social_instagram = $input->social_instagram;
        }
        if ($input->social_github !== null) {
            $meta->social_github = $input->social_github;
        }
        if ($input->code_head !== null) {
            $meta->code_head = $input->code_head;
        }
        if ($input->code_foot !== null) {
            $meta->code_foot = $input->code_foot;
        }
        if ($input->seo_indexing !== null) {
            $meta->seo_indexing = $input->seo_indexing;
        }
        if ($input->seo_robots_txt !== null) {
            $meta->seo_robots_txt = $input->seo_robots_txt;
        }
        if ($input->seo_external_links_follow !== null) {
            $meta->seo_external_links_follow = $input->seo_external_links_follow;
        }
        if ($input->seo_rich_schema !== null) {
            $meta->seo_rich_schema = $input->seo_rich_schema;
        }
        if ($input->comments_code !== null) {
            $meta->comments_code = $input->comments_code;
        }
        if ($input->newsletter_code !== null) {
            $meta->newsletter_code = $input->newsletter_code;
        }
        if ($input->color_modes !== null) {
            $meta->color_modes = $input->color_modes;
        }
        if ($input->color_mode_default !== null) {
            $meta->color_mode_default = $input->color_mode_default;
        }
        if ($input->syntax_on !== null) {
            $meta->syntax_on = $input->syntax_on;
        }
        if ($input->syntax_line_numbers !== null) {
            $meta->syntax_line_numbers = $input->syntax_line_numbers;
        }
        if ($input->syntax_theme !== null) {
            $meta->syntax_theme = $input->syntax_theme;
        }
        if ($input->heading_anchors !== null) {
            $meta->heading_anchors = $input->heading_anchors;
        }
        if ($input->link_analysis_enabled !== null) {
            $meta->link_analysis_enabled = $input->link_analysis_enabled;
        }
        if ($input->link_analysis_email_report !== null) {
            $meta->link_analysis_email_report = $input->link_analysis_email_report;
        }
        if ($input->ai_provider !== null) {
            $meta->ai_provider = $input->ai_provider;
        }
        if ($input->ai_translation_enabled !== null) {
            $meta->ai_translation_enabled = $input->ai_translation_enabled;
        }
        if ($input->ai_generation_enabled !== null) {
            $meta->ai_generation_enabled = $input->ai_generation_enabled;
        }

        $blog->setMeta($meta);

        $this->em->flush();

        $this->ed->dispatch(new BlogUpdatedEvent($blog, $blogOld));

        return $blog;
    }

    public function getBlogVariant(Blog $blog, Language $language): ?BlogVariant
    {
        return $this->em->getRepository(BlogVariant::class)->findOneBy([
            'blog' => $blog,
            'language' => $language,
        ]);
    }

    public function createBlogVariant(
        Blog $blog,
        Language $language,
        ?string $name = null,
        bool $flush = true
    ): BlogVariant
    {
        $variant = new BlogVariant();
        $variant->setBlog($blog);
        $variant->setLanguage($language);
        $variant->setName($name);

        $this->em->persist($variant);
        if ($flush) {
            $this->em->flush();
        }

        $blog->getVariants()->add($variant);

        return $variant;
    }

    public function updateBlogVariant(BlogVariant $variant, ?string $name, ?string $description): BlogVariant
    {
        $variantOld = clone $variant;

        if ($name !== null) {
            $variant->setName($name);
        }
        if ($description !== null) {
            $variant->setDescription($description);
        }

        $this->em->flush();

        $this->ed->dispatch(new BlogVariantUpdatedEvent($variant, $variantOld));

        return $variant;
    }

    /**
     * Soft-deletes the blog. The blog and its data are hard-deleted 30 days later
     * by BlogHardDeleteMessageHandler. See https://github.com/hyvor/core/issues/561
     */
    public function softDeleteBlog(Blog $blog): void
    {
        $blog->setDeletedAt($this->now());
        $this->em->flush();

        $this->ed->dispatch(new BlogDeletedEvent($blog));
    }

    public function hardDeleteBlog(Blog $blog): void
    {
        $this->em->remove($blog);
        $this->em->flush();
    }

}
