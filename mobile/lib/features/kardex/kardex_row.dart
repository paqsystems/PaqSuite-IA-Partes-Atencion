class KardexRow {
  const KardexRow({
    required this.id,
    required this.primary,
    required this.summary,
    this.statusLabel,
  });

  final String id;
  final String primary;
  final String summary;
  final String? statusLabel;
}