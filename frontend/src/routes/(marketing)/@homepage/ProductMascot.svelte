<script lang="ts">
	interface Props {
		color: string;
		size?: number;
	}

	let { color, size = 16 }: Props = $props();
</script>

<svg width={size} height={size} viewBox="0 0 132.292 132.292" xmlns="http://www.w3.org/2000/svg">
	<g stroke-width="7.158" stroke="#000" fill-rule="evenodd">
		<rect width="77.87" height="123.577" x="26.535" y="5.026" ry="38.935" fill="#fff3d3" />
		<path
			d="M105.079 65.75l.232 22.852c.219 21.569-16.969 39.11-38.538 39.329s-39.11-16.969-39.329-38.538l-.232-22.852z"
			fill={color}
		/>
	</g>
	<path
		d="M59.3 31.89c0-2.555-3.289-4.625-7.346-4.625s-7.346 2.071-7.346 4.625m43.076 0c0-2.555-3.289-4.625-7.346-4.625s-7.346 2.071-7.346 4.625m.271 15.859c0 2.555-3.289 4.625-7.346 4.625s-7.346-2.071-7.346-4.625"
		stroke="#000"
		paint-order="stroke fill markers"
		fill="none"
		stroke-linecap="round"
		stroke-linejoin="round"
		stroke-width="6.65"
	/>
</svg>
