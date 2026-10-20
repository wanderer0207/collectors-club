const buttons = document.querySelectorAll(".gachaButton");
const modal = document.getElementById("gachaModal");
const card = document.querySelector(".gacha-card");
const title = document.getElementById("gachaTitle");
const text = document.getElementById("gachaText");
const closeButton = document.getElementById("closeGacha");

let isRunning = false;
let gachaTimers = [];
let currentOripa = null;

function clearGachaTimers() {
    gachaTimers.forEach(clearTimeout);
    gachaTimers = [];
}

function schedule(callback, delay) {
    gachaTimers.push(setTimeout(callback, delay));
}

buttons.forEach(button => {
    button.addEventListener("click", event => {
        event.preventDefault();
        if (isRunning || !modal || !card) return;

        isRunning = true;
        currentOripa = button.dataset.oripa;
        startGacha();
    });
});

function startGacha() {
    clearGachaTimers();
    resetGacha();
    modal.classList.add("active", "slot-start");

    schedule(() => {
        title.textContent = "運命の抽選が始まる…";
        modal.classList.add("slot-reel");
    }, 450);

    schedule(() => {
        title.textContent = "CHANCE";
        modal.classList.add("slot-chance", "shake");
    }, 1900);

    schedule(() => {
        modal.classList.remove("shake");
        modal.classList.add("slot-lock");
        text.textContent = "最後の一瞬まで、目を離すな。";
    }, 2750);

    schedule(() => {
        modal.classList.remove("slot-reel", "slot-chance", "slot-lock", "slot-start");
        modal.classList.add("flash");
    }, 3500);

    schedule(() => {
        modal.classList.remove("flash");
        showResult();
    }, 4050);
}

async function showResult() {
    try {
        const response = await fetch("gacha.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ oripa: currentOripa })
        });

        if (!response.ok) throw new Error("HTTP Error: " + response.status);
        const result = await response.json();

        if (!result.success) {
            title.textContent = "抽選できませんでした";
            text.textContent = result.message || "時間をおいてもう一度お試しください。";
            card.textContent = "×";
            card.className = "gacha-card result failure";
            return;
        }

        const winner = result.card;
        const rarity = winner.rarity || "R";
        title.textContent = result.effect === "mythic" ? "GRAND JACKPOT" : "抽選結果";
        text.textContent = result.message;
        modal.classList.add("reveal", `effect-${result.effect || "standard"}`);
        card.className = `gacha-card result rarity-${rarity.toLowerCase()}`;
        card.replaceChildren();

        if (winner.image) {
            const image = document.createElement("img");
            image.src = `images/${encodeURIComponent(winner.image)}`;
            image.alt = winner.name || "当選カード";
            image.onerror = () => image.remove();
            card.append(image);
        }

        const badge = document.createElement("span");
        badge.className = "result-rarity";
        badge.textContent = rarity;
        card.append(badge);

        const winnerName = document.createElement("p");
        winnerName.className = "result-name";
        winnerName.textContent = winner.name || "カードを獲得しました";
        card.append(winnerName);
    } catch (error) {
        console.error("ガチャエラー:", error);
        title.textContent = "エラー";
        text.textContent = "ガチャ処理に失敗しました。";
        card.textContent = "×";
        card.className = "gacha-card result failure";
    } finally {
        isRunning = false;
    }
}

function resetGacha() {
    modal.classList.remove(
        "light", "shake", "explosion", "flash", "lightning-active",
        "slot-start", "slot-reel", "slot-chance", "slot-lock", "reveal",
        "effect-mythic", "effect-premium", "effect-chance", "effect-standard"
    );
    title.textContent = "運命の抽選が始まる";
    text.textContent = "GATE OF DESTINY";
    card.className = "gacha-card";
    card.textContent = "7";
}

closeButton?.addEventListener("click", closeGacha);
document.addEventListener("keydown", event => {
    if (event.key === "Escape") closeGacha();
});

function closeGacha() {
    if (!modal) return;
    clearGachaTimers();
    modal.classList.remove("active");
    resetGacha();
    isRunning = false;
}
