<script>
	import { Button } from "@hyvor/design/components";
import CustomerList from "../../customers/CustomerList.svelte";
import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
    import Review from "../new-home/@components/Review.svelte";
</script>


<div class="customers-section">
    <div class="title">
        <h1>
            Loved by Many & Highly Customizable!
        </h1>
        <h2>
            Hyvor Blogs is used by hundreds of bloggers around the world. Here are some of their blogs.
        </h2>
    </div>

    <div class="wrap hds-container">
        <CustomerList max={6} />
    </div>

    <div class="action">
        <Button as="a" href="/customers" color="gray" size="large">
            View more customer blogs &rarr;
        </Button>
    </div>
</div>

<style>
    .customers-section{
        padding-top: 100px;
    }

    .title {
        text-align: start;
        margin-bottom: 40px;
        padding-right: 50px;
        padding-left: 75px;
    }

    .title h1 {
        font-size: 60px !important;
        margin: 0 !important;
    }

    .title h2 {
        font-size: 20px !important;
        padding-top: 20px;
        margin: 0 !important;
        font-weight: normal;
    }
    .wrap {
        margin: 60px 55px;
        width: auto;
    }

    .action {
        margin-top: 50px;
        margin-bottom: 80px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .customers-section {
            padding-top: 60px;
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .title h1 {
            font-size: 28px !important;
            line-height: 1.3;
        }

        .title h2 {
            font-size: 16px !important;
            line-height: 1.4;
        }

        .wrap {
            margin: 30px 15px;
        }

        .action {
            margin-top: 30px;
            margin-bottom: 50px;
        }
    }

</style>
