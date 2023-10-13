function capture() {
    const captureElement = document.querySelector('#receipt')
    html2canvas(captureElement)
        .then(canvas => {
            canvas.style.display = 'none'
            document.body.appendChild(canvas)
            return canvas
        })
        .then(canvas => {
            const image = canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream')
            const a = document.createElement('a')
            a.setAttribute('download', 'RECEIPT.png')
            a.setAttribute('href', image)
            a.click()
            canvas.remove()
        })
}

const btn = document.querySelector('#download_receipt')
btn.addEventListener('click', capture)