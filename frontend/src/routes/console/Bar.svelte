<script lang="ts">
	import logo from '$lib/img/logo.png';
	import {
		ActionList,
		IconButton,
		ActionListItem,
		Button,
		Dropdown
	} from '@hyvor/design/components';
	import { authUserStore } from './lib/stores';
	import IconCaretDownFill from '@hyvor/icons/IconCaretDownFill';
import IconBell from '@hyvor/icons/IconBell';
import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
import IconChatFill from '@hyvor/icons/IconChatFill';
import IconDiscord from '@hyvor/icons/IconDiscord';

</script>

<div id="bar">
	<div class="inner hds-box">
		<div class="left">
			<a class="logo" href="/">
				<img src={logo} alt="Hyvor Blogs" />
				<span class="name">Hyvor Blogs</span>
			</a>
		</div>
		<div class="right">
			<Dropdown align="end" width={300}>
				{#snippet trigger()}
								<Button  variant="invisible" color="input" size="small">
						Community
						{#snippet end()}
										<IconCaretDownFill size={10}  />
									{/snippet}
					</Button>
							{/snippet}
				{#snippet content()}
								<ActionList >
						<a href="https://hyvor.community" target="_blank">
							<ActionListItem>
								Community Forum
								{#snippet description()}
														<div >hyvor.community</div>
													{/snippet}
								{#snippet start()}
														<IconChatFill  style="color:#666666" />
													{/snippet}
								{#snippet end()}
														<IconBoxArrowUpRight  size={12} />
													{/snippet}
							</ActionListItem>
						</a>
						<a href="https://hyvor.com/api/go/discord" target="_blank">
							<ActionListItem>
								Discord
								{#snippet description()}
														<div >HYVOR Discord Server</div>
													{/snippet}
								{#snippet start()}
														<IconDiscord  style="color:#7289da" />
													{/snippet}
								{#snippet end()}
														<IconBoxArrowUpRight  size={12} />
													{/snippet}
							</ActionListItem>
						</a>
					</ActionList>
							{/snippet}
			</Dropdown>

			<Dropdown align="end" width={300}>
				{#snippet trigger()}
								<Button  variant="invisible" color="input" size="small">
						Products
						{#snippet end()}
										<IconCaretDownFill size={10}  />
									{/snippet}
					</Button>
							{/snippet}
				{#snippet content()}
								<ActionList >
						<a href="https://blogs.hyvor.com" target="_blank">
							<ActionListItem>
								Hyvor Blogs
								{#snippet description()}
														<div >blogs.hyvor.com</div>
													{/snippet}
								{#snippet start()}
														<IconChatFill  />
													{/snippet}
								{#snippet end()}
														<IconBoxArrowUpRight  size={12} />
													{/snippet}
							</ActionListItem>
						</a>
						<a href="https://hyvor.com/api/go/discord" target="_blank">
							<ActionListItem>
								Discord
								{#snippet description()}
														<div >HYVOR Discord Server</div>
													{/snippet}
								{#snippet start()}
														<IconDiscord  />
													{/snippet}
								{#snippet end()}
														<IconBoxArrowUpRight  size={12} />
													{/snippet}
							</ActionListItem>
						</a>
					</ActionList>
							{/snippet}
			</Dropdown>

			<div class="notifications">
				<IconButton color="input" variant="invisible">
					<IconBell size={14} />
				</IconButton>
			</div>

			<div class="user">
				<img src={$authUserStore.picture_url} alt={$authUserStore.name} />
			</div>
		</div>
	</div>
</div>

<style>
	#bar {
		padding: 0 15px 10px 15px;
		padding-bottom: 0px;
	}
	.inner {
		padding: 10px 29px;
		display: flex;
		align-items: center;
		border-top-left-radius: 0;
		border-top-right-radius: 0;
	}
	img {
		width: 20px;
		height: 20px;
	}
	.left {
		display: flex;
		align-items: center;
		flex: 1;
	}
	.logo {
		display: flex;
		align-items: center;
		text-decoration: none;
		gap: 5px;
		font-weight: 600;
	}
	.user img {
		border-radius: 50%;
		width: 30px;
		height: 30px;
	}
	.user {
		display: inline-flex;
		align-items: center;
		margin-left: 5px;
	}
	.right {
		display: flex;
		align-items: center;
		gap: 10px;
	}
</style>
