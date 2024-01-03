<script lang="ts">
	import dayjs from "dayjs";
	import type { PaddleSubscriptionInfo } from "../../../lib/types";
	import FriendlyDate from "../../../lib/components/date/FriendlyDate.svelte";
	import { IconCreditCard2FrontFill } from "@hyvor/icons";
	import { Button } from "@hyvor/design/components";

    export let info: PaddleSubscriptionInfo;

    const paymentDaysDiffFromStartToEnd = info.next_payment ? 
        dayjs.unix(info.next_payment.at).diff(dayjs.unix(info.last_payment.at), 'd') : 
        0;

    const paymentDaysDiffFromTodayToEnd = info.next_payment ? 
        dayjs.unix(info.next_payment.at).diff(dayjs(), 'd') :
        0;
    
    const paymentWidth = (100 - (paymentDaysDiffFromTodayToEnd / paymentDaysDiffFromStartToEnd * 100)) + "%";

</script>



<div>
    <div class="payment-cycle card">
        <div class="payment-top">
            <div class="payment-last">
                <div class="payment-name">Last Payment</div>
                <div class="payment-amount">{ info.last_payment.amount } { info.last_payment.currency }
                </div>
            </div>
            <div class="payment-middle"/>
            <div class="payment-next">
                <div class="payment-name">Next Payment</div>
                <div class="payment-amount">{  
                    info.next_payment ? `${info.next_payment.amount } ${info.next_payment.currency}` : "-" }</div>
            </div>
        </div>
        <div class="payment-bar">
            <div
                class="payment-bar-fill"
                style="width: {paymentWidth}px"
            />
        </div>
        <div class="payment-top">
            <div class="payment-last">
                <FriendlyDate time={info.last_payment.at} />
            </div>
            <div class="payment-middle">
                {#if info.next_payment}
                    <div class="payment-left-days">
                        Next payment in {paymentDaysDiffFromTodayToEnd} days
                    </div>
                {/if}
            </div>
            <div class="payment-next">
                {#if info.next_payment}
                    <FriendlyDate time={info.next_payment.at} />
                {:else}
                    -
                {/if}
            </div>
        </div>

        
    </div>

    <div class="card">
        <div class="card-title">
            <div class="title-text">Payment Method</div>
            <Button
                as="a"
                href={info.update_url}
                target="_blank"
                size="small"
            >
                Update
            </Button>
        </div>
        <div class="card-desc">
            <div class="card-icon">
                <IconCreditCard2FrontFill size={150} />
            </div>
            <div class="card-details">
                <div>
                    <div class="card-detail-name">Type</div>
                    <div>{ info.card_brand.toUpperCase() }</div>
                </div>
                {#if info.card_last_four}
                    <div>
                        <div class="card-detail-name">Card Ending</div>
                        <div>{ info.card_last_four }</div>
                    </div>
                {/if}
                {#if info.card_expiration}
                    <div>
                        <div class="card-detail-name">Card Expiration</div>
                        <div>{ info.card_expiration }</div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>  


<style lang="scss">

    .payment-cycle {
        .payment-top {
            display: flex;
            font-weight: 600;
            align-items: center;
        }
        .payment-middle {
            flex: 1;
            text-align: center;
        }
        .payment-next .payment-amount {
            text-align: right;
        }
        .payment-amount {
            font-size: 18px;
            margin: 2px 0;
        }
        .payment-bar {
            margin: 6px 0;
            width: 100%;
            height: 15px;
            background: var(--accent-light);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }
        .payment-bar-fill {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background: var(--accent);
            border-radius: 20px;
            transition: 0.3s width ease-out;
        }
        .payment-left-days {
            text-align: center;
            font-size:12px;
            color: var(--text-light);
            font-weight: normal;
        }
    }

    .card {
        margin: 15px 0;
        border-radius: 20px;
        background-color: var(--input);
        padding:30px;


        .card-title {
            font-weight: 600;
            font-size: 16px;
            display: flex;
            .title-text {
                flex:1;
            }
            a {
                font-size:14px;
            }
        }
        .card-desc {
            display: flex;
            .card-details {
                padding: 30px;
                padding-right: 0;
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: space-evenly;
                font-weight: 600;
                & > div {
                    display: flex;
                }
                .card-detail-name {
                    flex: 1;
                    color: var(--text-light);
                }
            }
        }
    }

</style>