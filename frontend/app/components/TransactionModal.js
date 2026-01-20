"use client";
import { useState, useEffect } from "react";
import { deposit, withdraw, transfer, getUsers, getAccounts } from "@/lib/api";

export default function TransactionModal({ account, type, onClose, onSuccess }) {
  const [balance, setbalance] = useState("");
  const [destinationAccountId, setDestinationAccountId] = useState("");
  const [allAccounts, setAllAccounts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    if (type === "transfer") {
      fetchAllAccounts();
    }
  }, [type]);

  const fetchAllAccounts = async () => {
    try {
      const token = localStorage.getItem("token");
      const usersData = await getUsers(token);
      const usersList = usersData.users || usersData;

      const accountsList = [];
      for (const user of usersList) {
        try {
          const userAccounts = await getAccounts(user.id);
          const accountsWithUser = (userAccounts.accounts || userAccounts)
            .filter(acc => acc.id !== account.id && acc.status === "active")
            .map(acc => ({
              ...acc,
              userName: `${user.firstName} ${user.lastName}`,
            }));
          accountsList.push(...accountsWithUser);
        } catch (err) {
          console.error(`Erreur pour l'utilisateur ${user.id}`, err);
        }
      }
      setAllAccounts(accountsList);
    } catch (err) {
      setError("Erreur lors du chargement des comptes");
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");

    const balanceValue = parseFloat(balance);
    
    if (isNaN(balanceValue) || balanceValue <= 0) {
      setError("Le montant doit être supérieur à 0");
      return;
    }

    if (type === "withdraw" && balanceValue > account.balance) {
      setError("Solde insuffisant");
      return;
    }

    if (type === "transfer" && !destinationAccountId) {
      setError("Veuillez sélectionner un compte destinataire");
      return;
    }

    if (type === "transfer" && balanceValue > account.balance) {
      setError("Solde insuffisant pour effectuer le transfert");
      return;
    }

    setLoading(true);

    try {
      const data = {
        account_id: account.id,
        amount: balanceValue,
      };

      if (type === "deposit") {
        await deposit(data);
      } else if (type === "withdraw") {
        await withdraw(data);
      } else if (type === "transfer") {
        await transfer({
          from_account_id: account.id,
          to_account_id: destinationAccountId,
          amount: balanceValue,  
        });
      }

      onSuccess();
    } catch (err) {
      setError("Erreur lors de la transaction");
    } finally {
      setLoading(false);
    }
  };

  const getTitle = () => {
    switch (type) {
      case "deposit":
        return "💰 Recharger le compte";
      case "withdraw":
        return "💸 Retrait d'argent";
      case "transfer":
        return "🔄 Transfert d'argent";
      default:
        return "Transaction";
    }
  };

  const getButtonText = () => {
    switch (type) {
      case "deposit":
        return "Recharger";
      case "withdraw":
        return "Retirer";
      case "transfer":
        return "Transférer";
      default:
        return "Confirmer";
    }
  };

  const getButtonColor = () => {
    switch (type) {
      case "deposit":
        return "bg-green-600 hover:bg-green-700";
      case "withdraw":
        return "bg-orange-600 hover:bg-orange-700";
      case "transfer":
        return "bg-blue-600 hover:bg-blue-700";
      default:
        return "bg-indigo-600 hover:bg-indigo-700";
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-gray-800">{getTitle()}</h2>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-600 text-2xl"
          >
            ×
          </button>
        </div>

        {/* Informations du compte */}
        <div className="bg-gray-50 rounded-lg p-4 mb-6">
          <p className="text-sm text-gray-600 mb-1">Compte source</p>
          <p className="font-mono font-medium text-gray-900">
            {account.accountNumber}
          </p>
          <p className="text-sm text-gray-600 mt-2 mb-1">Solde actuel</p>
          <p className="font-bold text-lg text-gray-900">
            {new Intl.NumberFormat('fr-FR', {
              style: 'currency',
              currency: 'XAF'
            }).format(account.balance)}
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Montant (FCFA)
            </label>
            <input
              type="number"
              value={balance}
              onChange={(e) => setbalance(e.target.value)}
              required
              min="0.01"
              step="0.01"
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
              placeholder="0.00"
            />
          </div>

          {type === "transfer" && (
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Compte destinataire
              </label>
              <select
                value={destinationAccountId}
                onChange={(e) => setDestinationAccountId(e.target.value)}
                required
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
              >
                <option value="">Sélectionner un compte</option>
                {allAccounts.map((acc) => (
                  <option key={acc.id} value={acc.id}>
                    {acc.account_number} - {acc.usern_ame} ({new Intl.NumberFormat('fr-FR', {
                      style: 'currency',
                      currency: 'XAF'
                    }).format(acc.balance)})
                  </option>
                ))}
              </select>
            </div>
          )}

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
              {error}
            </div>
          )}

          <div className="flex space-x-3 pt-4">
            <button
              type="button"
              onClick={onClose}
              className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Annuler
            </button>
            <button
              type="submit"
              disabled={loading}
              className={`flex-1 px-4 py-2 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${getButtonColor()}`}
            >
              {loading ? "Traitement..." : getButtonText()}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}