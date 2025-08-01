const fs = require('fs');
const { exec } = require('child_process');
const path = require('path');

/**
 * 📊 Collecteur de Métriques Système
 * 
 * Ce script collecte les métriques du système pour le monitoring
 * et peut être utilisé par le script ubuntu-manager.sh
 */

class SystemMetrics {
    constructor() {
        this.dbPath = '/var/www/dashboard-multi-modules/database.db';
        this.configPath = '/var/www/dashboard-multi-modules/config/config.json';
    }

    // Exécuter une commande shell
    execCommand(command) {
        return new Promise((resolve, reject) => {
            exec(command, (error, stdout, stderr) => {
                if (error) {
                    resolve(''); // Retourner vide en cas d'erreur plutôt que rejeter
                    return;
                }
                resolve(stdout.trim());
            });
        });
    }

    // Métriques système de base
    async getSystemMetrics() {
        try {
            // CPU Usage
            const cpuUsage = await this.execCommand("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | awk -F'%' '{print $1}'");
            
            // Memory
            const memInfo = await this.execCommand("free | grep Mem");
            const memParts = memInfo.split(/\s+/);
            const memTotal = parseInt(memParts[1]) || 0;
            const memUsed = parseInt(memParts[2]) || 0;
            const memPercent = memTotal > 0 ? Math.round((memUsed / memTotal) * 100) : 0;
            
            // Disk
            const diskInfo = await this.execCommand("df / | tail -1");
            const diskParts = diskInfo.split(/\s+/);
            const diskPercent = parseInt(diskParts[4]?.replace('%', '')) || 0;
            
            // Load Average
            const loadAvg = await this.execCommand("uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//'");
            
            // Uptime
            const uptime = await this.execCommand("uptime -p");
            
            return {
                cpu: parseFloat(cpuUsage) || 0,
                memory: {
                    total: Math.round(memTotal / 1024), // MB
                    used: Math.round(memUsed / 1024),   // MB
                    percent: memPercent
                },
                disk: {
                    percent: diskPercent,
                    used: diskParts[2] ? Math.round(parseInt(diskParts[2]) / 1024 / 1024 * 10) / 10 : 0, // GB
                    total: diskParts[1] ? Math.round(parseInt(diskParts[1]) / 1024 / 1024 * 10) / 10 : 0  // GB
                },
                loadAverage: parseFloat(loadAvg) || 0,
                uptime: uptime || 'Unknown'
            };
        } catch (error) {
            return {
                cpu: 0,
                memory: { total: 0, used: 0, percent: 0 },
                disk: { percent: 0, used: 0, total: 0 },
                loadAverage: 0,
                uptime: 'Error'
            };
        }
    }

    // Métriques des services
    async getServiceMetrics() {
        try {
            // PM2 Status
            const pm2Status = await this.execCommand("pm2 jlist 2>/dev/null || echo '[]'");
            let pm2Data = { total: 0, online: 0, processes: [] };
            
            try {
                const pm2Json = JSON.parse(pm2Status);
                pm2Data.total = pm2Json.length;
                pm2Data.online = pm2Json.filter(p => p.pm2_env?.status === 'online').length;
                pm2Data.processes = pm2Json.map(p => ({
                    name: p.name,
                    status: p.pm2_env?.status || 'unknown',
                    uptime: p.pm2_env?.pm_uptime ? new Date(p.pm2_env.pm_uptime).toISOString() : null,
                    memory: p.monit?.memory || 0,
                    cpu: p.monit?.cpu || 0
                }));
            } catch (e) {
                // Garder les valeurs par défaut
            }

            // Services systemd
            const nginxStatus = await this.execCommand("systemctl is-active nginx 2>/dev/null");
            const phpStatus = await this.execCommand("systemctl is-active php8.1-fpm 2>/dev/null");
            
            // Port status
            const port80 = await this.execCommand("netstat -tlnp 2>/dev/null | grep ':80 ' | wc -l");
            const port443 = await this.execCommand("netstat -tlnp 2>/dev/null | grep ':443 ' | wc -l");

            return {
                pm2: pm2Data,
                nginx: nginxStatus === 'active',
                php: phpStatus === 'active',
                ports: {
                    http: parseInt(port80) > 0,
                    https: parseInt(port443) > 0
                }
            };
        } catch (error) {
            return {
                pm2: { total: 0, online: 0, processes: [] },
                nginx: false,
                php: false,
                ports: { http: false, https: false }
            };
        }
    }

    // Métriques de la base de données
    async getDatabaseMetrics() {
        try {
            if (!fs.existsSync(this.dbPath)) {
                return { exists: false };
            }

            const sqlite3 = require('sqlite3').verbose();
            
            return new Promise((resolve) => {
                const db = new sqlite3.Database(this.dbPath, (err) => {
                    if (err) {
                        resolve({ exists: false, error: err.message });
                        return;
                    }

                    const metrics = { exists: true };

                    // Taille du fichier
                    const stats = fs.statSync(this.dbPath);
                    metrics.size = {
                        bytes: stats.size,
                        mb: Math.round(stats.size / 1024 / 1024 * 100) / 100
                    };

                    // Intégrité
                    db.get("PRAGMA integrity_check", (err, row) => {
                        metrics.integrity = !err && row?.integrity_check === 'ok';

                        // Nombre de tables
                        db.get("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'", (err, row) => {
                            metrics.tables = row?.count || 0;

                            // Utilisateurs
                            db.get("SELECT COUNT(*) as count FROM users", (err, row) => {
                                metrics.users = {
                                    total: row?.count || 0
                                };

                                // Nouveaux utilisateurs aujourd'hui
                                db.get("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = DATE('now')", (err, row) => {
                                    metrics.users.today = row?.count || 0;

                                    // Dernière activité
                                    db.get("SELECT MAX(updated_at) as last FROM users", (err, row) => {
                                        metrics.lastActivity = row?.last || null;

                                        db.close();
                                        resolve(metrics);
                                    });
                                });
                            });
                        });
                    });
                });
            });
        } catch (error) {
            return { exists: false, error: error.message };
        }
    }

    // Métriques réseau et logs
    async getNetworkMetrics() {
        try {
            // Nginx access logs
            const nginxAccess = await this.execCommand("tail -1000 /var/log/nginx/dashboard-access.log 2>/dev/null | wc -l");
            const nginxErrors = await this.execCommand("tail -100 /var/log/nginx/dashboard-error.log 2>/dev/null | wc -l");
            
            // Requêtes récentes (dernière heure approximative)
            const recentRequests = await this.execCommand(`awk '$4 > "'$(date -d '1 hour ago' '+%d/%b/%Y:%H:%M:%S')'" {count++} END {print count+0}' /var/log/nginx/dashboard-access.log 2>/dev/null`);

            return {
                nginx: {
                    totalRequests: parseInt(nginxAccess) || 0,
                    recentRequests: parseInt(recentRequests) || 0,
                    errors: parseInt(nginxErrors) || 0
                },
                timestamp: new Date().toISOString()
            };
        } catch (error) {
            return {
                nginx: { totalRequests: 0, recentRequests: 0, errors: 0 },
                timestamp: new Date().toISOString()
            };
        }
    }

    // Collecte complète de toutes les métriques
    async collectAll() {
        const [system, services, database, network] = await Promise.all([
            this.getSystemMetrics(),
            this.getServiceMetrics(),
            this.getDatabaseMetrics(),
            this.getNetworkMetrics()
        ]);

        return {
            timestamp: new Date().toISOString(),
            system,
            services,
            database,
            network
        };
    }

    // Sauvegarder les métriques dans un fichier JSON
    async saveMetrics(filePath = '/tmp/system-metrics.json') {
        try {
            const metrics = await this.collectAll();
            fs.writeFileSync(filePath, JSON.stringify(metrics, null, 2));
            return { success: true, file: filePath };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    // Afficher un résumé console
    async displaySummary() {
        const metrics = await this.collectAll();
        
        console.log('🖥️  MÉTRIQUES SYSTÈME');
        console.log('═══════════════════════');
        console.log(`CPU: ${metrics.system.cpu}%`);
        console.log(`RAM: ${metrics.system.memory.percent}% (${metrics.system.memory.used}MB/${metrics.system.memory.total}MB)`);
        console.log(`Disque: ${metrics.system.disk.percent}% (${metrics.system.disk.used}GB/${metrics.system.disk.total}GB)`);
        console.log(`Uptime: ${metrics.system.uptime}`);
        
        console.log('\n🚀 SERVICES');
        console.log('═══════════');
        console.log(`PM2: ${metrics.services.pm2.online}/${metrics.services.pm2.total} processus actifs`);
        console.log(`Nginx: ${metrics.services.nginx ? '✅ Actif' : '❌ Inactif'}`);
        console.log(`PHP-FPM: ${metrics.services.php ? '✅ Actif' : '❌ Inactif'}`);
        
        if (metrics.database.exists) {
            console.log('\n🗄️ BASE DE DONNÉES');
            console.log('═══════════════════');
            console.log(`Taille: ${metrics.database.size.mb}MB`);
            console.log(`Intégrité: ${metrics.database.integrity ? '✅ OK' : '❌ Problème'}`);
            console.log(`Utilisateurs: ${metrics.database.users.total} (${metrics.database.users.today} nouveaux aujourd'hui)`);
        }
        
        console.log('\n🌐 RÉSEAU');
        console.log('═══════════');
        console.log(`Requêtes récentes: ${metrics.network.nginx.recentRequests}`);
        console.log(`Erreurs: ${metrics.network.nginx.errors}`);
        
        console.log(`\n⏰ Dernière mise à jour: ${new Date(metrics.timestamp).toLocaleString()}`);
    }
}

// CLI Usage
if (require.main === module) {
    const metrics = new SystemMetrics();
    
    const action = process.argv[2] || 'summary';
    
    switch (action) {
        case 'summary':
            metrics.displaySummary();
            break;
        case 'save':
            const file = process.argv[3] || '/tmp/system-metrics.json';
            metrics.saveMetrics(file).then(result => {
                if (result.success) {
                    console.log(`✅ Métriques sauvegardées dans ${result.file}`);
                } else {
                    console.error(`❌ Erreur: ${result.error}`);
                }
            });
            break;
        case 'json':
            metrics.collectAll().then(data => {
                console.log(JSON.stringify(data, null, 2));
            });
            break;
        default:
            console.log('Usage: node system-metrics.js [summary|save|json]');
            break;
    }
}

module.exports = SystemMetrics; 