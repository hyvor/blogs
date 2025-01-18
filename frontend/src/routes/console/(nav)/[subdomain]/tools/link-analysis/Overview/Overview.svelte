<script lang="ts">
	import { Button, toast } from "@hyvor/design/components";
    import { linkAnalysisChecks, startFullAnalysis, type Stats } from "../linkAnalysisActions";
	import Stat from "./Stat.svelte";
	import { createEventDispatcher } from "svelte";
	import Analyses from "./Analyses.svelte";
    interface Props {
        stats: Stats;
    }

    let { stats }: Props = $props();

    const dispatch = createEventDispatcher();

    let isStartingNewAnalysis = $state(false);

    function handleStartNewAnalysis() {
        isStartingNewAnalysis = true;

        const toastId = toast.loading('Starting new analysis...');

        startFullAnalysis()
            .then(check => {
                toast.success('New analysis started.', {id: toastId});
                linkAnalysisChecks.update(checks => {
                    return [check, ...checks];
                });
            })
            .catch(e => {
                toast.error(e.message || 'Failed to start new analysis.', {id: toastId});
            })
            .finally(() => {
                isStartingNewAnalysis = false;
            })
    }

</script>

<div class="stats">

    <div class="stats-top">

        <div class="stats-top-left">
            <div class="stats-title">
                Stats
            </div>


            <div class="stats-note">
                Stats are based on the currently analyzed posts. Some posts may not be analyzed until a full-blog analysis is done.
            </div>
        </div>

        <div class="stats-top-right">
            <Button
                size="small"
                on:click={() => dispatch('links')}
            >
                See Links
            </Button>
        </div>


    </div>

    <div class="stats-inner">

        <div class="stats-row">

            <Stat
                value={stats.counts.ok}
                status={200}
            />
            <Stat
                value={stats.counts.broken} 
                status={404}
            />
            <Stat
                value={stats.counts.redirect} 
                status={301}
            />
            <Stat
                value={stats.counts.ignored} 
                status={-2}
            />

        </div>

    </div>

    <div class="stats analyses">

        <div class="stats-top">

            <div class="stats-top-left">

                <div class="stats-title">
                    Analyses
                </div>

                <div class="stats-note">
                    A full-blog analysis is done every 2 weeks automatically (only published posts are analyzed). You can also start one manually. 
                </div>

            </div>

            <div class="stats-top-right">

                <Button 
                    size="small" 
                    on:click={handleStartNewAnalysis}
                    disabled={isStartingNewAnalysis}
                >
                    Start New Analysis
                </Button>

            </div>

        </div>

        <div class="analyses-table">
            <Analyses />
        </div>

    </div>

</div>

<style lang="scss">

    .stats {
        .stats-top {
            display: flex;
        }
        .stats-top-left {
            flex: 1;
        }

        .stats-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .stats-inner {
            background-color:var(--input);
            border-radius: 20px;
            padding: 10px;
        }
        .stats-row {
            display: flex;
            justify-content: space-around
        }
        .stats-note {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 10px;
            max-width: 500px;
        }
    }

    .analyses {
        margin-top: 25px;
        .analyses-table {
            margin-top: 10px;
        }
    }

</style>