"use client";
import { useState, useEffect } from "react";
import { getAccounts, deactivateAccount } from "@/lib/api";
import TransactionModal from "./TransactionModal";

export default function UserDetailsModal({ user, onClose }) {
  const [accounts, setAccounts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedAccount, setSelectedAccount] = useState(null);
  const [transactionType, setTransactionType] = useState(null);

  useEffect(() => {
    fetchAccounts();
  }, [user.id]);

  const fetchAccounts = async () => {
    try {
      setLoading(true);
      const data = await getAccounts(user.id);
      setAccounts(data.accounts || data);
      setError("");
    } catch (err) {
      setError("Erreur lors du chargement des comptes");
    } finally {
      setLoading(false);
    }
  };

  const handleDeactivate = async (accountId) => {
    if (!confirm("Êtes-vous sûr de vouloir désactiver ce compte ?")) {
      return;
    }

    try {
      await deactivateAccount(accountId);
      await fetchAccounts();
    } catch (err) {
      alert("Erreur lors de la désactivation du compte");
    }
  };

  const handleTransaction = (account, type) => {
    setSelectedAccount(account);
    setTransactionType(type);
  };

  const handleTransactionSuccess = () => {
    setSelectedAccount(null);
    setTransactionType(null);
    fetchAccounts();
  };

  const formatCurrency = (balance) => {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'XAF'
    }).format(balance);
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR');
  };

  return (
    <>
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg shadow-xl w-full max-w-4xl p-6 max-h-[90vh] overflow-y-auto">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h2 className="text-2xl font-bold text-gray-800">
                Détails de l'utilisateur
              </h2>
              <p className="text-gray-600 mt-1">
                {user.name}
              </p>
            </div>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-gray-600 text-2xl"
            >
              ×
            </button>
          </div>

          {/* Informations utilisateur */}
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 className="font-semibold text-gray-800 mb-3">Informations personnelles</h3>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-gray-600">Email</p>
                <p className="font-medium text-gray-900">{user.email}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Téléphone</p>
                <p className="font-medium text-gray-900">{user.phone || "N/A"}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Rôle</p>
                <span className={`inline-flex px-2 py-1 text-xs leading-5 font-semibold rounded-full ${
                  user.role === "admin"
                    ? "bg-purple-100 text-purple-800"
                    : "bg-green-100 text-green-800"
                }`}>
                  {user.role || "user"}
                </span>
              </div>
              <div>
                <p className="text-sm text-gray-600">ID Utilisateur</p>
                <p className="font-medium text-gray-900">{user.id}</p>
              </div>
            </div>
          </div>

          {/* Comptes bancaires */}
          <div>
            <h3 className="font-semibold text-gray-800 mb-3">
              Comptes bancaires ({accounts.length})
            </h3>

            {loading ? (
              <div className="text-center py-8 text-gray-600">
                Chargement des comptes...
              </div>
            ) : error ? (
              <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {error}
              </div>
            ) : accounts.length === 0 ? (
              <div className="text-center py-8 text-gray-500 bg-gray-50 rounded-lg">
                Aucun compte bancaire
              </div>
            ) : (
              <div className="space-y-3">
                {accounts.map((account) => (
                  <div
                    key={account.id}
                    className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                  >
                    <div className="flex justify-between items-start mb-3">
                      <div className="flex-1">
                        <div className="flex items-center space-x-3 mb-2">
                          <span className="font-mono text-sm font-medium text-gray-900">
                            {account.accountNumber}
                          </span>
                          <span className="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {account.accountType || "courant"}
                          </span>
                          <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                            account.status === "active"
                              ? "bg-green-100 text-green-800"
                              : "bg-red-100 text-red-800"
                          }`}>
                            {account.status === "active" ? "Actif" : "Inactif"}
                          </span>
                        </div>
                        <div className="grid grid-cols-2 gap-3 text-sm">
                          <div>
                            <p className="text-gray-600">Solde</p>
                            <p className="font-bold text-lg text-gray-900">
                              {formatCurrency(account.balance)}
                            </p>
                          </div>
                          <div>
                            <p className="text-gray-600">Date de création</p>
                            <p className="font-medium text-gray-900">
                              {formatDate(account.created_at)}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>

                    {/* Actions sur le compte */}
                    {account.status === "active" && (
                      <div className="flex space-x-2 pt-3 border-t border-gray-200">
                        <button
                          onClick={() => handleTransaction(account, 'deposit')}
                          className="flex-1 px-3 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition-colors font-medium"
                        >
                          💰 Recharger
                        </button>
                        <button
                          onClick={() => handleTransaction(account, 'withdraw')}
                          className="flex-1 px-3 py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600 transition-colors font-medium"
                        >
                          💸 Retirer
                        </button>
                        <button
                          onClick={() => handleTransaction(account, 'transfer')}
                          className="flex-1 px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors font-medium"
                        >
                          🔄 Transférer
                        </button>
                        <button
                          onClick={() => handleDeactivate(account.id)}
                          className="px-3 py-2 bg-red-100 text-red-600 text-sm rounded-lg hover:bg-red-200 transition-colors font-medium"
                        >
                          🚫 Désactiver
                        </button>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>

          <div className="mt-6 flex justify-end">
            <button
              onClick={onClose}
              className="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>

      {/* Modal de transaction */}
      {selectedAccount && transactionType && (
        <TransactionModal
          account={selectedAccount}
          type={transactionType}
          onClose={() => {
            setSelectedAccount(null);
            setTransactionType(null);
          }}
          onSuccess={handleTransactionSuccess}
        />
      )}
    </>
  );
}