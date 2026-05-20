import Chart from 'chart.js/auto';

export function createChart(
	title: string,
	labels: string[],
	data: number[],
	canvas: HTMLCanvasElement,
	type: 'bar' | 'line' = 'bar'
): Chart {
	return new Chart(canvas, {
		type: type,
		options: {
			interaction: {
				intersect: false,
				mode: 'index'
			},
			plugins: {
				legend: {
					display: false
				},
				tooltip: {
					displayColors: false
				}
			},
			scales: {
				x: {
					ticks: {
						autoSkip: true,
						maxTicksLimit: 10
					},
					grid: {
						display: false
					}
				},
				y: {
					border: {
						display: false
					}
				}
			},
			elements: {
				point: {
					radius: 0
				},
				line: {
					tension: 0.4
				}
			}
		},
		data: {
			labels,
			datasets: [
				{
					label: title,
					data,
					backgroundColor: '#000',
					borderColor: '#777',
					borderWidth: type === 'line' ? 2 : 3,
					borderRadius: 5
				}
			]
		}
	});
}
