<script lang="ts">
    // export let userName = "John Doe";
    // export let country = "United States";
    // export let date = "2024-01-15";
    // export let review = "Excellent service! The product exceeded my expectations and the customer support was outstanding. Highly recommend to anyone looking for quality and reliability.";
    // export let stars = 5;
    // export let likes = 12;
    // export let verified = true;
    // export let userAvatar = null

    import IconCheck from "@hyvor/icons/IconCheck";
    import type { Snippet, Component } from "svelte";
    import IconStarFill from "@hyvor/icons/IconStarFill";
    interface Props {
        userName: string;
        country: string;
        title?: string;
        date: string;
        review: string;
        stars: number;
        verified?: boolean;
        userAvatar?: string | null;
        children?: Snippet;
        icon: Component
    }

    let {
        userName,
        country,
        title,
        date,
        review,
        stars,
        verified = false,
        userAvatar = null
    }: Props = $props();

    // Format date to readable format
     function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }

    // Generate initials from username for avatar
    function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    }

    // Generate star rating
    function getStarRating(rating) {
        return Array.from({length: 5}, (_, i) => i < rating);
    }
</script>

<div class="review-card">
    <div class="review-header">
        <div class="user-info">
            <div class="user-avatar">
                {#if userAvatar}
                    <img src={userAvatar} alt={userName} />
                {:else}
                    <span class="initials">{getInitials(userName)}</span>
                {/if}
            </div>
            <div class="user-details">
                <h3 class="user-name">{userName}</h3>
                <div class="user-meta">
                    <span class="country">{country}</span>
                    {#if verified}
            <span class="verified">
              <IconCheck size={14} />
              Verified
            </span>
                    {/if}
                </div>
            </div>
        </div>
        <div class="review-date">
            {formatDate(date)}
        </div>
    </div>

    <div class="star-rating">
        {#each getStarRating(stars) as filled}
            <IconStarFill class="star {filled ? 'filled' : ''}" size={18} color="#00b67a" />
        {/each}
        <span class="rating-text">({stars}/5)</span>
    </div>

    <div class="review-content">
        <h3>{title}</h3>
        <p>{review}</p>
    </div>
</div>

<style>
    .review-card {
        background: white;
        border: 1px solid var(--gray-light);
        border-radius: 20px;
        padding: 20px;
        width: 400px;
        max-width: 100%;
        box-shadow: var(--box-shadow);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #00b67a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 16px;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .user-name {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .user-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #6b7280;
    }

    .verified {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #00b67a;
        font-weight: 500;
    }

    .review-date {
        font-size: 14px;
        color: #6b7280;
    }

    .star-rating {
        display: flex;
        align-items: center;
        gap: 2px;
        margin-bottom: 16px;
    }

    .star {
        color: #ddd;
    }

    .star.filled {
        color: #00b67a;
    }

    .rating-text {
        margin-left: 8px;
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }

    .review-content {
        margin-bottom: 20px;
    }

    .review-content p {
        font-size: 16px;
        line-height: 1.6;
        color: #374151;
        margin: 0;
    }

    .country::before {
        content: "🌍 ";
    }

    @media (max-width: 480px) {
        .review-card {
            padding: 16px;
        }

        .review-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .user-info {
            width: 100%;
        }
    }
</style>
