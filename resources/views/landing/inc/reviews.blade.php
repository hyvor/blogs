<?php

use App\Domains\App\MarketingService;

$reviews = [
    [
        'name' => 'Christian',
        'role' => 'Small Business Owner',
        'review' => 'The interface is so easy for our team. The price points have made increasing the team size affordable and the output is everything we expect.',
        'platform' => 'g2',
        'link' => 'https://www.g2.com/products/hyvor-blogs/reviews/hyvor-blogs-review-8225262'
    ],
    [
        'name' => 'Emad',
        'role' => 'Blogger',
        'image' => 'https://res.cloudinary.com/dqabfne6s/image/upload/v1687765525/blogs.hyvor.com/reviews-images/AAuE7mCwDlSHz4D1y_2oXQo_Ci0aTpe8gZZUV5VAdmvsZAs01_un5aod.jpg',
        'review' => 'I\'m impressed by the level of details in every part that each element can be translated. The level of details in the settings is insane',
        'platform' => 'appsumo',
        'link' => 'https://appsumo.com/products/hyvor-blogs/reviews/best-customizable-blogging-platform-for-1195710/',
    ],
    [
        'name' => 'Kol-yan',
        'role' => 'Blogger',
        'review' => 'I have already convinced myself that this is the best choice for a blog when flexibility is needed, like an open API, self-hosting, and various fine-tuning tools.',
        'platform' => 'appsumo',
        'link' => 'https://appsumo.com/products/hyvor-blogs/reviews/thanks-for-the-excellent-blogging-tool-1190249/'
    ]
];

$blogsCount = MarketingService::getBlogsCount();

?>

<div class="reviews-page">

    <div class="love-wall">

        <h3>Loved by&nbsp;<span data-typewriter="Bloggers...,Startups...,Developers..."></span></h3>

        <div class="description">
            Hyvor Blogs powers <span class="power">{{ number_format($blogsCount) }}</span> blogs and counting<sup>*</sup>
            <div class="note">*Updated daily.</div>
        </div>

        <div class="reviews">

            <svg style="display: none">
                <defs>
                    <symbol id="review-star" viewBox="0 0 16 16">
                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"
                              fill="currentColor"
                        />
                    </symbol>
                </defs>
            </svg>

            @foreach ($reviews as $review)

                <div class="review">

                    <div class="stars">
                        @foreach(range(1,5) as $i)
                            <svg width="16" height="16">
                                <use href="#review-star"/>
                            </svg>
                        @endforeach
                    </div>

                    <div class="review-text">
                        "{{ $review['review'] }}"
                    </div>

                    <div class="review-bottom">
                        <div class="user">
                            @if (isset($review['image']))
                                <img src="{{ $review['image'] }}" alt="{{ $review['name'] }}"/>
                            @else
                                <img src="/img/landing/default-avatar.jpeg
                                "
                                     alt="{{ $review['name'] }}"/>
                            @endif
                            <div class="name-role">
                                <div class="name">{{ $review['name'] }}</div>
                                <div class="role">{{ $review['role'] }}</div>
                            </div>
                        </div>
                        <div class="platform">
                            <a href="{{ $review['link'] }}" target="_blank" rel="nofollow">

                                @if ($review['platform'] === 'appsumo')
                                    <svg width="19" height="15" viewBox="0 0 19 15" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.729 12.7131L1.61601 14.2641C1.39697 14.2638 1.18267 14.2003 0.998813 14.0813C0.81496 13.9622 0.669371 13.7927 0.579522 13.5929C0.489673 13.3931 0.459381 13.1717 0.492282 12.9551C0.525184 12.7386 0.61988 12.5361 0.765005 12.3721C3.35701 9.42108 8.76501 3.00708 13.781 3.00708C14.8193 3.00684 15.8362 3.30199 16.713 3.85808C19.813 5.80708 18.851 9.30608 17.962 11.2581C17.7665 11.6877 17.4526 12.0527 17.0572 12.3104C16.6617 12.5681 16.201 12.7078 15.729 12.7131V12.7131Z"
                                              fill="#FFBC00"/>
                                        <path d="M13.762 1.495C8.427 1.495 3.145 7.34 0.179001 10.727C0.0448492 10.5193 -0.0167241 10.273 0.00390044 10.0266C0.024525 9.78017 0.126186 9.54753 0.293001 9.365C2.884 6.413 8.295 3.59401e-06 13.308 3.59401e-06C14.348 -0.00118812 15.3668 0.294006 16.245 0.851004C17.0562 1.34248 17.6917 2.07736 18.061 2.951C17.8922 2.81438 17.7152 2.68814 17.531 2.573C16.4027 1.86606 15.0975 1.49237 13.766 1.495"
                                              fill="#FFBC00"/>
                                    </svg>
                                @endif

                                @if ($review['platform'] === 'g2')
                                    <img src="{{ asset('img/landing/g2.ico') }}" alt="G2 Logo" width="20">
                                @endif

                            </a>
                        </div>
                    </div>


                </div>

            @endforeach


        </div>

    </div>

</div>


<script>

    class Typerwriter {
        constructor(el, options) {
            this.el = el;
            this.words = [...this.el.dataset.typewriter.split(',')];
            this.speed = options?.speed || 100;
            this.delay = options?.delay || 1500;
            this.repeat = options?.repeat;
            this.initTyping();
        }

        wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

        toggleTyping = () => this.el.classList.toggle('typing');

        async typewrite(word) {
            await this.wait(this.delay);
            this.toggleTyping();
            for (const letter of word.split('')) {
                this.el.textContent += letter;
                await this.wait(this.speed)
            }
            this.toggleTyping();
            await this.wait(this.delay);
            this.toggleTyping();
            while (this.el.textContent.length !== 0) {
                this.el.textContent = this.el.textContent.slice(0, -1);
                await this.wait(this.speed)
            }
            this.toggleTyping();
        }

        async initTyping() {
            for (const word of this.words) {
                await this.typewrite(word);
            }
            if (this.repeat) {
                await this.initTyping();
            } else {
                this.el.style.animation = 'none';
            }
        }
    }

    document.querySelectorAll('[data-typewriter]').forEach(el => {
        new Typerwriter(el, {
            repeat: true,
        })
    })

</script>