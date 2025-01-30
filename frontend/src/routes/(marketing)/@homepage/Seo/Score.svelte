<script lang="ts">

   interface Props {
      name: string;
      score: number;
      our?: boolean;
   }

   let { name, score, our = false }: Props = $props();
</script>

<div class="wrap" class:our={our}>
    <div class="score">{score}</div>
    <div class="name">
        {name}
    </div>
</div>

<style>
    .wrap {   
        display: inline-flex;
        flex-direction: column;
        align-items: center;
    }

    .score {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #ebf9f0;
        color: #008800;
        width: 100px;
        height: 100px;
        font-size:25px;
        border: 8px solid #00cc66;
    }
   
    .wrap.our .score {
        background-color: #9ADE7B;
        color: #557C55;
        border: 8px solid #557C55;
    }
    
    .name {
        margin-top: 15px;
        font-size: 18px;
        font-weight: 600;
    }

</style>