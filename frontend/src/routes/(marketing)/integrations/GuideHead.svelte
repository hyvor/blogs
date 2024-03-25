<script lang="ts">
    import { IconBoxes } from '@hyvor/icons';
import hyvorblogsIcon from './icons/hyvorblogs.png'
    export let title: string
    export let description: string | null = '';
    export let integrationIcon: string | null = '';
    import { Button } from '@hyvor/design/components';
</script>




<div class="hds-container guide-head">
    <div class="hds-container buttons">
        <div class="button">
            <Button as="a" href="/integrations" size="large" variant="outline">
               <IconBoxes slot="start" /> View All Integrations
            </Button>
        </div> 
    </div>


    <div class="left">

        {#if integrationIcon}
            <div class="int">
                <img src={integrationIcon} alt="Integration icon" class="int-icon" />
                <span>+</span>
                <img src={hyvorblogsIcon} alt="Hyvor Blogs" class="int-icon" />
            </div>
        {/if}

        {#if title }
            <h1>
                {title}
            </h1>
        {/if}

        {#if description}
            <h2>
                {description}
            </h2>
        {/if}

        <!-- <div class="buttons">
            <Button as="a" href="/console?signup" size="large">
                Start your blog
            </Button>
        </div> -->

    </div>

</div>


<style lang="scss">
    .buttons {
    //    center the content
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        .button {
            margin-bottom: 50px;
        }
    }
    .guide-head {
        display: flex;
        padding-top: 50px;
        // //center align the whole content
        align-items: center;
        justify-content: center;
        flex-direction: column;
        .button {
            display: flex;
            //align the button to left
            justify-content:flex-start;
        }
        .int {
            display: flex;
            align-items: center;
            gap: 25px;
            font-size: 35px;
            margin-bottom: 30px;
            justify-content: center;
            span {
                opacity: 0.2;
            }
        }
        .int-icon {
            max-width: 100px;
            max-height: 100px;
            
            object-fit: cover;
            
        }
        h1 {
            margin: 0;
            font-size:40px;
            text-align: center;
        }
        h2 {
            font-weight: normal;
            font-size: 20px;
            color: var(--text-light);
            margin: 0 auto;
            padding-top: 20px;
            text-align: center;
            width: 600px;
            max-width: 100%;
        }       
    }
    @media (max-width: 992px) {
        .guide-head {
            flex-direction: column;
            text-align: center;
            // .buttons {
            //     justify-content: center;
            // }
        }
        .int {
            justify-content: center;
        }
    }
</style>