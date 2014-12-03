PDF_AUX=pdf-aux

report.pdf: report.tex
	pdflatex -output-directory=$(PDF_AUX) $<
	xdg-open $(PDF_AUX)/report.pdf > /dev/null 2>&1
	ln -f $(PDF_AUX)/report.pdf

clean:
	rm -f $(PDF_AUX)/* report.pdf
