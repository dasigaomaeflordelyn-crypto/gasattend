document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!window.confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
    });
  });

  const exportButton = document.getElementById('export-csv');
  if (exportButton) {
    exportButton.addEventListener('click', () => {
      const table = document.getElementById('report-table');
      if (!table) return;
      const rows = [...table.querySelectorAll('tr')].map(row =>
        [...row.querySelectorAll('th,td')].map(cell => {
          const value = cell.innerText.replace(/\s+/g, ' ').trim();
          return '"' + value.replace(/"/g, '""') + '"';
        }).join(',')
      );
      const blob = new Blob(['\uFEFF' + rows.join('\r\n')], {type: 'text/csv;charset=utf-8;'});
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'gasattend-report.csv';
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    });
  }
});
