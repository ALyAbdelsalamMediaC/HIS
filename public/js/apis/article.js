export function likeArticle(mediaId, csrfToken) {
    return fetch(`/article/${mediaId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(response => response.json());
}

export function unlikeArticle(mediaId, csrfToken) {
    return fetch(`/article/${mediaId}/like`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(response => response.json());
}
