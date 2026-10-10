// Minimal JSON POST for the few panel actions that are not page visits
// (e.g. AI drafts). Sends Laravel's XSRF cookie so CSRF protection applies.
export async function postJson(url, data) {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': match ? decodeURIComponent(match[1]) : '',
        },
        body: JSON.stringify(data),
    });

    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(body.message || `Request failed (${response.status}).`);
    }

    return body;
}
