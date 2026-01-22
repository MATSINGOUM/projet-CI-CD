// ================================
// BASE URLS (MICROSERVICES)
// ================================
const AUTH_API_URL = "/api/users/";     
const BANK_API_URL = "/api/accounts/";      

//const AUTH_API_URL = "http://localhost:8000/";      
//const BANK_API_URL = "http://localhost:8001/";

// ================================
// AUTH
// ================================
export async function login(email, password) {
  const res = await fetch(`${AUTH_API_URL}api/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });
  return res.json();
}

export async function register(data) {
  const res = await fetch(`${AUTH_API_URL}api/register`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  return res.json();
}

export async function logout(token) {
  await fetch(`${AUTH_API_URL}api/logout`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
}

// ================================
// USERS (microservice auth)
// ================================
export async function getUsers(token) {
  const res = await fetch(`${AUTH_API_URL}api/users`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return res.json();
}

export async function deleteUser(id, token) {
  await fetch(`${AUTH_API_URL}api/users/${id}`, {
    method: "DELETE",
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
}

// ================================
// ACCOUNTS (microservice bancaire)
// ================================
export async function getAccounts(userId) {
  const res = await fetch(`${BANK_API_URL}api/users/${userId}/accounts`);
  return res.json();
}

export async function createAccount(data) {
  const res = await fetch(`${BANK_API_URL}api/accounts`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  return res.json();
}

export async function deactivateAccount(id) {
  await fetch(`${BANK_API_URL}api/accounts/${id}/deactivate`, {
    method: "PATCH",
  });
}

// ================================
// TRANSACTIONS
// ================================
export async function deposit(data) {
  const res = await fetch(`${BANK_API_URL}api/deposit`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  return res.json();
}

export async function withdraw(data) {
  const res = await fetch(`${BANK_API_URL}api/withdraw`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  return res.json();
}

export async function transfer(data) {
  const res = await fetch(`${BANK_API_URL}api/transfer`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  return res.json();
}

export async function getTransactions(accountId) {
  const res = await fetch(
    `${BANK_API_URL}api/accounts/${accountId}/transactions`
  );
  return res.json();
}
