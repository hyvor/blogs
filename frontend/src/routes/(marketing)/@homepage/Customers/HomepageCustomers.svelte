<script>
	import { Button } from "@hyvor/design/components";
import CustomerList from "../../customers/CustomerList.svelte";
import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
    import Review from "../new-home/@components/Review.svelte";
</script>

<FeatureSectionTitle
    title="Loved by Many & Highly Customizable!"
    subtitle="Hyvor Blogs is used by hundreds of bloggers around the world. Here are some of their blogs."
/>

<div class="wrap hds-container">
    <CustomerList max={6} />
</div>

<div class="reviews">
<!--    <Review-->
<!--        userName="Alice Johnson"-->
<!--        title="Game Changer for My Blog!"-->
<!--        country="Canada"-->
<!--        date="2024-05-10"-->
<!--        review="The ability to add custom codes has transformed how I manage my blog. Integrating analytics and marketing tools is now seamless!"-->
<!--        stars={5}-->
<!--        verified={true}-->
<!--        />-->

<!--    <Review-->
<!--        userName="Michael Smith"-->
<!--        title="Perfect for My Business Blog"-->
<!--        country="USA"-->
<!--        date="2024-04-22"-->
<!--        review="Hyvor Blogs offers the perfect balance of customization and ease of use. The custom code feature allowed me to integrate essential marketing tools effortlessly."-->
<!--        stars={4}-->
<!--        verified={true}-->
<!--        />-->

</div>

<div class="action">
    <Button as="a" href="/customers" color="gray">
        View more customer blogs &rarr;
    </Button>
</div>

<style>
    .wrap {
        margin: 60px 55px;
        width: auto;
    }

    .action {
        margin-top: 50px;
        margin-bottom: 80px;
        text-align: center;
    }
</style>
