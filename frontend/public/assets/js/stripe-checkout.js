document.addEventListener("click", async (e) => {
  if (!e.target.classList.contains("stripe-buy")) return;

  const name = e.target.dataset.name;
  const price_cents = parseInt(e.target.dataset.price, 10);

  const res = await fetch("http://localhost:4567/create-checkout-session", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, price_cents })
  });

  const data = await res.json();

  const stripe = Stripe("pk_test_51SPi5VBpgkgfy6xhLkftEC4eQDe83eGDcusAj1kz03Oapy9ABv7c8UwfDEQciresq6Z0bdNV8GvGGUQ3KE0MK1du00DZVhQvac"); // MUST BE PUBLISHABLE KEY

  stripe.redirectToCheckout({ sessionId: data.id });
});
