jQuery(document).ready(function() {

    QUnit.test("include", function (assert) {
        let test3 = document.getElementById('data').dataset.urltest3;
        let basepath = document.getElementById('data').dataset.basepath;

        assert.timeout(5000);
        const done = assert.async();
        const done2 = assert.async();

        jQuery.include(
            [
                basepath + 'tests/testinc1.js',
                [test3, 'js']
            ],
            function () {
                assert.equal($("#includeresult").text(), 'INC1INC3', "include loaded");
                jQuery.include(basepath + 'tests/testinc2.js', function () {
                    assert.equal($("#includeresult").text(), 'INC1INC3INC2', "include loaded");
                    done2();
                })
                done();
            }
        );
    });
});