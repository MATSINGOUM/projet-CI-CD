"use client";

import { useEffect, useState } from "react";
import { getUsers, deleteUser } from "@/lib/api";
import Link from "next/link";

export default function UsersPage() {
  const [users, setUsers] = useState([]);

  useEffect(() => {
    const token = localStorage.getItem("token");
    getUsers(token).then(setUsers);
  }, []);

  async function handleDelete(id) {
    const token = localStorage.getItem("token");
    await deleteUser(id, token);
    setUsers(users.filter(u => u.id !== id));
  }

  return (
    <div className="max-w-5xl mx-auto p-6">
      <h2 className="text-2xl font-bold mb-6">Utilisateurs</h2>

      <div className="space-y-4">
        {users.map(user => (
          <div
            key={user.id}
            className="bg-white rounded-lg shadow p-4 flex justify-between items-center"
          >
            <div>
              <p className="font-semibold">{user.name}</p>
              <p className="text-sm text-gray-500">{user.email}</p>
            </div>

            <div className="space-x-2">
              <Link
                href={`/dashboard/accounts/${user.id}`}
                className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
              >
                Comptes
              </Link>

              <button
                onClick={() => handleDelete(user.id)}
                className="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
              >
                Supprimer
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
