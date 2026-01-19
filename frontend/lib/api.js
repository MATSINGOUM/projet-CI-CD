const API_URL = "http://localhost:8000/api";

export async function login(email, password) {
  const res = await fetch(`${API_URL}/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  });
  return res.json();
}

export async function getUsers(token) {
  const res = await fetch(`${API_URL}/users`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return res.json();
}

export async function deleteUser(id, token) {
  await fetch(`${API_URL}/users/${id}`, {
    method: "DELETE",
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
}

export async function getAccounts(userId) {
  const res = await fetch(`${API_URL}/users/${userId}/accounts`);
  return res.json();
}

export async function deactivateAccount(id) {
  await fetch(`${API_URL}/accounts/${id}/deactivate`, {
    method: "PATCH",
  });
}
