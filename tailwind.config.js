/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./src/**/*.{js,jsx,ts,tsx,html}",
    ],
    theme: {
        extend: {
            backgroundImage: {
                iphone: 'url("/src/public/iphoneXMock.png")',
                messageMock: 'url("/src/public/messageMock.svg")',
            },
        },
    },
    plugins: [],
}