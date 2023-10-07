<?php

declare(strict_types=1);

namespace Whateverthing\Tundra\Api\Mastodon\Entities;

/**
 * Mastodon Status (aka "Post" or "Toot")
 *
 * @see https://docs.joinmastodon.org/entities/Status/
 */
class Status
{
    public readonly Account $account;
    public readonly array $media_attachments;

    public function __construct(
        public readonly string $id,
        public readonly string $created_at,
        public readonly ?string $in_reply_to_id,
        public readonly ?string $in_reply_to_account_id,
        public readonly bool $sensitive,
        public readonly string $spoiler_text,
        public readonly string $visibility,
        public readonly ?string $language,
        public readonly string $uri,
        public readonly string $url,
        public readonly int $replies_count,
        public readonly int $reblogs_count,
        public readonly int $favourites_count,
        public readonly ?string $edited_at,
        public readonly string $content,
        public readonly mixed $reblog,
        array|Account $account,
        array $media_attachments,
        public readonly array $mentions,
        public readonly array $tags,
        public readonly array $emojis,
        public readonly mixed $card,
        public readonly mixed $poll,
        public readonly mixed $application = null,
        public readonly mixed $favourited = null,
        public readonly mixed $reblogged = null,
        public readonly mixed $muted = null,
        public readonly mixed $bookmarked = null,
        public readonly mixed $pinned = null,
        public readonly mixed $filtered = null,
        ...$leftovers,
    ) {
        $this->account = new Account(...$account);

        $attachments = [];
        foreach ($media_attachments as $attachment) {
            $attachments[] = new MediaAttachment(...$attachment);
        }
        $this->media_attachments = $attachments;

        if ($leftovers) {
            throw new \Exception('Leftovers! ' . print_r($leftovers, true));
        }
    }

    public function simpleArray(): array
    {
        $output = [];

        $output['From'] = $this->account->display_name
            ? $this->account->display_name . ' (@' . $this->account->acct . ')'
            : '@' . $this->account->acct;
        $output['From'] = trim($output['From'], "'");
        $output['CreatedAt'] = $this->created_at;

        if ($this->edited_at) {
            $output['EditedAt'] = $this->edited_at;
        }

        if ($this->in_reply_to_id) {
            $output['ReplyTo'] = $this->in_reply_to_id;
            $output['ReplyToAccount'] = $this->in_reply_to_account_id;
        }

        $output['uri'] = $this->uri;
        $output['htmlcontent'] = $this->content;
        $output['contentStripped'] = strip_tags(str_replace('<br>', PHP_EOL, $this->content));

        if ($this->tags) {
            $output['tags'] = '';
            foreach ($this->tags as $tag) {
                $output['tags'] .= '#' . $tag['name'] . ' ';
            }
        }

        if ($this->poll) {
            $pollOptionsAndVotes = '';
            foreach ($this->poll->options as $option) {
                $pollOptionsAndVotes .= $option['title'] . ' (votes: ' . $option['votes_count'] . ')' . PHP_EOL;
            }

            $output['poll'] = $pollOptionsAndVotes;
        }

        if ($this->emojis) {
            $output['emojis'] = json_encode($this->emojis);
        }

        return $output;
    }
}
