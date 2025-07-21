export function likeVideo(mediaId, csrfToken) {
    return fetch(`/media/${mediaId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(response => response.json());
}

export function unlikeVideo(mediaId, csrfToken) {
    return fetch(`/media/${mediaId}/like`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(response => response.json());
}
