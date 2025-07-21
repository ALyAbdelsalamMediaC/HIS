export function submitReviewerRate(mediaId, userId, rate, csrfToken) {
    return fetch('/reviews/rate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            media_id: mediaId,
            user_id: userId,
            rate: rate
        })
    }).then(response => response.json());
}

export function submitAdminRate(mediaId, userId, rate, csrfToken) {
    return fetch('/admins/rate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            media_id: mediaId,
            user_id: userId,
            rate: rate
        })
    }).then(response => response.json());
}
