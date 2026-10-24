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
 * Reads a snapshot from the central registry over HTTPS. The central recognises the site by its domain
 * (the obligatory X-Aravo-Site header) and by its server IP; a bearer token is only for a server that is not ours.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class central {
    /**
     * Fetches the snapshot. Returns an array with "httpstatus" (int), "body" (string or null) and "error"
     * (a short code or null). It never throws.
     *
     * @param string $endpoint The full endpoint URL (must be https).
     * @param string $token The optional bearer token, or an empty string.
     * @param string $siteurl The site wwwroot, sent as X-Aravo-Site.
     * @param string|null $from Start of the window as YYYY-MM-DD, or null for the default.
     * @param string|null $to End of the window as YYYY-MM-DD, or null for the default.
     * @return array
     */
    public static function fetch(
        string $endpoint,
        string $token,
        string $siteurl,
        ?string $from = null,
        ?string $to = null
    ): array {
        if (strpos($endpoint, 'https://') !== 0) {
            return ['httpstatus' => 0, 'body' => null, 'error' => 'badurl'];
        }
        $params = [];
        if ($from !== null && $from !== '') {
            $params['from'] = $from;
        }
        if ($to !== null && $to !== '') {
            $params['to'] = $to;
        }
        $url = new \moodle_url($endpoint, $params);
        $headers = [
            'X-Aravo-Site: ' . $siteurl,
            'Accept: application/json',
        ];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        global $CFG;
        // The curl class lives in filelib.php, which a scheduled task does not load on its own.
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader($headers);
        $options = [
            'CURLOPT_TIMEOUT' => 15,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_FOLLOWLOCATION' => 0,
        ];
        $body = $curl->get($url->out(false), [], $options);
        $info = $curl->get_info();
        $httpstatus = (int) ($info['http_code'] ?? 0);
        if ($curl->get_errno()) {
            return ['httpstatus' => $httpstatus, 'body' => null, 'error' => 'network'];
        }
        return ['httpstatus' => $httpstatus, 'body' => is_string($body) ? $body : null, 'error' => null];
    }
}
