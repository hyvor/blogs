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
	import {
		IconCaretDownFill,
		IconBell,
		IconBoxArrowUpRight,
		IconChatFill,
		IconDiscord
	} from '@hyvor/icons';
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
				<Button slot="trigger" variant="invisible" color="input" size="small">
					Community
					<IconCaretDownFill size={10} slot="end" />
				</Button>
				<ActionList slot="content">
					<a href="https://hyvor.community" target="_blank">
						<ActionListItem>
							Community Forum
							<div slot="description">hyvor.community</div>
							<IconChatFill slot="start" style="color:#666666" />
							<IconBoxArrowUpRight slot="end" size={12} />
						</ActionListItem>
					</a>
					<a href="https://hyvor.com/api/go/discord" target="_blank">
						<ActionListItem>
							Discord
							<div slot="description">HYVOR Discord Server</div>
							<IconDiscord slot="start" style="color:#7289da" />
							<IconBoxArrowUpRight slot="end" size={12} />
						</ActionListItem>
					</a>
				</ActionList>
			</Dropdown>

			<Dropdown align="end" width={300}>
				<Button slot="trigger" variant="invisible" color="input" size="small">
					Products
					<IconCaretDownFill size={10} slot="end" />
				</Button>
				<ActionList slot="content">
					<a href="https://blogs.hyvor.com" target="_blank">
						<ActionListItem>
							Hyvor Blogs
							<div slot="description">blogs.hyvor.com</div>
							<IconChatFill slot="start" />
							<IconBoxArrowUpRight slot="end" size={12} />
						</ActionListItem>
					</a>
					<a href="https://hyvor.com/api/go/discord" target="_blank">
						<ActionListItem>
							Discord
							<div slot="description">HYVOR Discord Server</div>
							<IconDiscord slot="start" />
							<IconBoxArrowUpRight slot="end" size={12} />
						</ActionListItem>
					</a>
				</ActionList>
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
