<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_oksigeniaaravo\local;

/**
 * A snapshot read from the central registry (the contract is in the agent docs, aravo.snapshot.v1). It only reads the
 * decoded data; it never recalculates balances or allocations, because the central is the source of truth.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snapshot {
    /** @var array The decoded snapshot. */
    private $data;

    /**
     * Wraps a decoded snapshot.
     *
     * @param array $data The decoded snapshot JSON.
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * The snapshot held in the local copy, or null if there is none yet.
     *
     * @return self|null
     */
    public static function from_store(): ?self {
        $row = store::get();
        if (!$row || empty($row->payload)) {
            return null;
        }
        $data = json_decode((string) $row->payload, true);
        if (!is_array($data)) {
            return null;
        }
        return new self($data);
    }

    /**
     * When the central generated the snapshot, as a Unix time (0 if unknown).
     *
     * @return int
     */
    public function generated_at(): int {
        return empty($this->data['generated_at']) ? 0 : (int) strtotime((string) $this->data['generated_at']);
    }

    /**
     * The provider name for the block title, or an empty string.
     *
     * @return string
     */
    public function provider(): string {
        return isset($this->data['client']['provider']) ? (string) $this->data['client']['provider'] : '';
    }

    /**
     * The plan code (aula, centro, institucion, mantenimiento), or an empty string.
     *
     * @return string
     */
    public function plan(): string {
        return isset($this->data['client']['plan']) ? (string) $this->data['client']['plan'] : '';
    }

    /**
     * Whether the client has a bag, so the block paints that section.
     *
     * @return bool
     */
    public function has_bag(): bool {
        return !empty($this->data['bag']['has_bag']);
    }

    /**
     * The bag, with minutes as integers and the balance never negative.
     *
     * @return array
     */
    public function bag(): array {
        $bag = isset($this->data['bag']) && is_array($this->data['bag']) ? $this->data['bag'] : [];
        $items = [];
        foreach (($bag['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $items[] = [
                'id' => (string) ($item['id'] ?? ''),
                'purchasedon' => (string) ($item['purchased_on'] ?? ''),
                'minutes' => (int) ($item['minutes'] ?? 0),
                'expireson' => isset($item['expires_on']) && $item['expires_on'] !== null
                    ? (string) $item['expires_on'] : '',
                'remaining' => (int) ($item['remaining_minutes'] ?? 0),
                'reference' => isset($item['reference']) && $item['reference'] !== null
                    ? (string) $item['reference'] : '',
            ];
        }
        return [
            'purchased' => (int) ($bag['purchased_minutes'] ?? 0),
            'used' => (int) ($bag['used_minutes'] ?? 0),
            'remaining' => max(0, (int) ($bag['balance_minutes'] ?? 0)),
            'threshold' => (int) ($bag['low_threshold_minutes'] ?? 0),
            'items' => $items,
        ];
    }

    /**
     * This month's included interventions.
     *
     * @return array
     */
    public function interventions(): array {
        $in = isset($this->data['interventions']) && is_array($this->data['interventions'])
            ? $this->data['interventions'] : [];
        $items = [];
        foreach (($in['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $items[] = [
                'date' => (string) ($item['date'] ?? ''),
                'minutes' => (int) ($item['minutes'] ?? 0),
            ];
        }
        return [
            'month' => (string) ($in['month'] ?? ''),
            'allowed' => (int) ($in['allowed'] ?? 0),
            'used' => (int) ($in['used'] ?? 0),
            'maxeach' => (int) ($in['max_minutes_each'] ?? 0),
            'items' => $items,
        ];
    }

    /**
     * The work entries of the window, most recent first, with the allocation the central already calculated.
     *
     * @return array
     */
    public function entries(): array {
        $out = [];
        foreach (($this->data['entries'] ?? []) as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $out[] = [
                'id' => (string) ($entry['id'] ?? ''),
                'date' => (string) ($entry['date'] ?? ''),
                'description' => (string) ($entry['description'] ?? ''),
                'minutes' => (int) ($entry['minutes'] ?? 0),
                'type' => (string) ($entry['type'] ?? ''),
                'included' => (int) ($entry['included_minutes'] ?? 0),
                'bag' => (int) ($entry['bag_minutes'] ?? 0),
                'outofbag' => (int) ($entry['out_of_bag_minutes'] ?? 0),
                'reference' => isset($entry['reference']) && $entry['reference'] !== null
                    ? (string) $entry['reference'] : '',
                'service' => isset($entry['service']) && $entry['service'] !== null
                    ? (string) $entry['service'] : '',
            ];
        }
        return $out;
    }

    /**
     * The per-month summary of the window, most recent first.
     *
     * @return array
     */
    public function monthly_summary(): array {
        $out = [];
        foreach (($this->data['monthly_summary'] ?? []) as $month) {
            if (!is_array($month)) {
                continue;
            }
            $out[] = [
                'month' => (string) ($month['month'] ?? ''),
                'allowed' => (int) ($month['interventions_allowed'] ?? 0),
                'used' => (int) ($month['interventions_used'] ?? 0),
                'bagminutes' => (int) ($month['bag_minutes'] ?? 0),
                'outofbagminutes' => (int) ($month['out_of_bag_minutes'] ?? 0),
                'maintenanceminutes' => (int) ($month['maintenance_minutes'] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * The plan limits for this client (active users and disk in GB), or null if the central did not send them.
     *
     * @return array|null
     */
    public function limits(): ?array {
        $limits = isset($this->data['client']['limits']) && is_array($this->data['client']['limits'])
            ? $this->data['client']['limits'] : null;
        if ($limits === null) {
            return null;
        }
        $users = (int) ($limits['active_users'] ?? 0);
        $disk = (float) ($limits['disk_gb'] ?? 0);
        if ($users <= 0 && $disk <= 0) {
            return null;
        }
        return ['active_users' => $users, 'disk_gb' => $disk];
    }

    /**
     * A notice the central may send to show on top of the block and the register (for example, to mark a demo site as
     * showing sample data), or null when there is none. The text comes ready from the central; the block only shows it.
     *
     * @return array|null With 'label' (a short tag, may be empty) and 'text' (the line).
     */
    public function notice(): ?array {
        $notice = isset($this->data['notice']) && is_array($this->data['notice']) ? $this->data['notice'] : null;
        if ($notice === null) {
            return null;
        }
        $text = isset($notice['text']) ? trim((string) $notice['text']) : '';
        if ($text === '') {
            return null;
        }
        return [
            'label' => isset($notice['label']) ? trim((string) $notice['label']) : '',
            'text' => $text,
        ];
    }

    /**
     * The central admin link for site administrators, or an empty string.
     *
     * @return string
     */
    public function central_admin_url(): string {
        return isset($this->data['links']['central_admin']) ? (string) $this->data['links']['central_admin'] : '';
    }
}
