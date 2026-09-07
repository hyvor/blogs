<script>
    import {DocsImage} from '@hyvor/design/marketing';
    import { Callout } from '@hyvor/design/components';
</script>

# Agent (AI)

Hyvor Blogs offers an AI agent that can access your posts and provide suggestions to improve them. You can enable or disable the AI agent anytime at **Console &rarr; Settings &rarr; AI**.

- [Features](#features)
- [Providers & Models](#providers-models)
- [How to Access the AI Agent](#access)
- [Review Document Changes](#review-changes)
- [Pricing](#pricing)

<h2 id="features">Features</h2>

What AI Agent can do:

- Read your posts
- Read blog settings: tags, authors, languages, etc.
- Summarize posts
- Answer questions related to your posts
- Suggest improvements to your posts
- Generate content ideas for your posts

What AI Agent cannot do:

- Edit posts without your permission, always require your approval (see [Review Document Changes](#review-changes))
- Make changes to blog settings
- Delete posts or any other content

<h2 id="providers-models">Providers & Models</h2>

Hyvor Blogs supports a couple of AI providers and models:

- **OpenAI** (USA)
  - `gpt-5.6-luna` (default)
  - `gpt-5.6-terra`
  - `gpt-5.6-sol`
- **Anthropic** (USA)
  - `claude-sonnet-5`
  - `claude-opus-5`
- **Mistral** (France)
  - `mistral-small-latest`
  - `mistral-medium-latest`
  - `mistral-large-latest`
  - `zai-glm-5-2`

You can change the model in the **Console &rarr; Settings &rarr; AI** section. For most content-oriented tasks (suggesting improvements, summarizing, generating content ideas), even the smallest models work well. You can choose a larger model if you need more advanced capabilities. See [pricing](#pricing) below for details.

<h2 id="access">
    How to Access the AI Agent
</h2>

AI agent is available at **Console &rarr; Agent** and **within the post editor**. Both agents provide the same functionality, but the agent in the post editor is instructed to help you primarily with the post you are editing.

<DocsImage src="/images/docs/agent/agent-in-editor.png" width={700} />

<h2 id="review-changes">Review Document Changes</h2>

An AI agent can suggest changes to your posts. Click "Review Changes" to see the suggested edits and approve or reject each one.

Here's an example of suggested changes by the AI agent to fix grammar mistakes:

<DocsImage src="/images/docs/agent/review-changes-button.png" width={400} />

When you click "Review Changes", a panel will appear allowing you to review each suggested change and either approve or reject one by one.

<DocsImage src="/images/docs/agent/review-modal.png" width={900} />

Once done, click "Apply Changes" to save the post.

<Callout type="info">
    If you applied changes to a published or scheduled posts, you will need to update the post in the post editor to reflect those changes.
</Callout>

<h2 id="pricing">Pricing</h2>

Each [pricing plan](/pricing) comes with an AI usage quota for your organization that will be used by all blogs under that organization. In **Console & Billing**, you can view your current usage and remaining quota. It resets on the first day of each month.

Larger, expensive models consume more of your AI usage quota. **Console &rarr; Settings &rarr; AI** shows a relative comparison of the models' consumption. We recommend starting with smaller models and only using larger models when necessary.

<h2 id="faq">FAQ</h2>

**Can I ask the AI agent to bypass approval and make changes directly?**<br />
No, the AI agent cannot bypass the approval process. All changes must be reviewed and approved by you manually.

**Can I use a custom model (self-hosted)?**<br />
No, we currently do not support custom models.
