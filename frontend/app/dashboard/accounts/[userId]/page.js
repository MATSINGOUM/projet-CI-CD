"use client";

import { useEffect, useState } from "react";
import { getAccounts, deactivateAccount } from "@/lib/api";

export default function AccountsPage({ params }) {
  const [accounts, setAccounts] = useState([]);

  useEffect(() => {
    getAccounts(params.userId).then(setAccounts);
  }, []);

  async function handleDeactivate(id) {
    await deactivateAccount(id);
    setAccounts(
      accounts.map(a =>
        a.id === id ? { ...a, is_active: false } : a
      )
    );
  }

  return (
    <div className="max-w-5xl mx-auto p-6">
      <h2 className="text-2xl font-bold mb-6">
        Comptes bancaires
      </h2>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {accounts.map(acc => (
          <div
            key={acc.id}
            className="bg-white p-5 rounded-lg shadow"
          >
            <p className="font-mono text-sm text-gray-500">
              {acc.account_number}
            </p>

            <p><b>Type :</b> {acc.type}</p>
            <p><b>Solde :</b> {acc.balance} €</p>
            <p>
              <b>Status :</b>{" "}
              <span
                className={`font-semibold ${
                  acc.is_active
                    ? "text-green-600"
                    : "text-red-600"
                }`}
              >
                {acc.is_active ? "Actif" : "Inactif"}
              </span>
            </p>

            {acc.is_active && (
              <button
                onClick={() => handleDeactivate(acc.id)}
                className="mt-4 w-full bg-yellow-600 text-white py-2 rounded hover:bg-yellow-700"
              >
                Désactiver
              </button>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
