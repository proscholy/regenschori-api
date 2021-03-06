<script>
/**
 * Base Vue object to extend from other ModelEdit.vue files.
 *
 * Concepts:
 *  this.model_database
 *  - is loaded from graphql, immutable and copied into data.model
 *  - is used for comparing if the form has changed (isDirty) or resetting to the db's state
 *
 *  this.model
 *  - is current state-handling object, mirroring all the updates on the frontend
 *  - has only fields defined in the fragment (see this.fragment)
 *
 *  this.enums
 *  - is a storage for all possible values for inputs such as Select Boxes
 *
 *  this.fragment
 *  - is a type of GraphQL fragment (see https://graphql.org/learn/queries/#fragments)
 *    with model's editable (fillable) attributes and relations
 *  - each model has it's own defined in admin/models/Model.js
 *
 */

export default {
    props: ['preset-id'],

    data() {
        return {
            redirUrl: '',
            isLocked: false
        };
    },

    computed: {
        isDirty() {
            if (this.is_deleted) return false;
            if (!this.model_database) return false;
            //   if (!this.model.url) return true;

            for (let field of this._getFieldsFromFragment(this.fragment)) {
                if (field === 'authors_pivot') {
                    let model_authors = this.model[field];
                    let ml_db_authors = this.model_database[field];
                    model_authors = model_authors.filter(
                        el => el.author != null
                    );
                    ml_db_authors = ml_db_authors.filter(
                        el => el.author != null
                    );
                    model_authors.sort((a, b) =>
                        a.author.name > b.author.name
                            ? 1
                            : b.author.name > a.author.name
                            ? -1
                            : 0
                    );
                    ml_db_authors.sort((a, b) =>
                        a.author.name > b.author.name
                            ? 1
                            : b.author.name > a.author.name
                            ? -1
                            : 0
                    );
                    model_authors.forEach(function(v) {
                        delete v.id;
                        delete v.__typename;
                    });
                    ml_db_authors.forEach(function(v) {
                        delete v.id;
                        delete v.__typename;
                    });

                    if (!_.isEqual(model_authors, ml_db_authors)) {
                        console.log(
                            'Dirty check found mismatch on the field ' + field
                        );
                        return true;
                    }

                    continue;
                }

                let model_field = this.model[field];
                model_field = model_field === '' ? null : model_field;

                if (!_.isEqual(model_field, this.model_database[field])) {
                    console.log(
                        'Dirty check found mismatch on the field ' + field
                    );
                    return true;
                }
            }

            if (typeof this.isDirtyChecker == 'function') {
                return this.isDirtyChecker();
            }

            return false;
        }
    },

    watch: {
        isDirty: function(val) {
            if (!val && this.redirUrl) {
                this.goToPage(this.redirUrl, false);
                this.redirUrl = '';
            }
        },

        isLocked: function(val) {
            if (val && !document.getElementById('locked')) {
                document
                    .getElementsByClassName('container')[0]
                    .setAttribute('style', 'display:none');
                var lockedDiv = document.createElement('div');
                lockedDiv.id = 'locked';
                lockedDiv.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="h3">Je třeba chvilku počkat…</h2>
                            <p>
                                Vypadá to, že tenhle záznam právě upravuje někdo jiný.
                                <br>Abychom předešli možným problémům, dočasně jsme režim úprav uzamkli.
                                <br><br><button type="button" class="btn btn-outline-primary" @click="refreshUpdating()">Zkusit znovu</button>
                                <br><br><img src="https://thumbs.gfycat.com/FoolishHonorableArgentinehornedfrog-size_restricted.gif" alt="You shall not pass">
                            </p>
                        </div>
                    </div>
                `;
                document
                    .getElementsByClassName('application--wrap')[0]
                    .appendChild(lockedDiv);
            } else if (document.getElementById('locked')) {
                document
                    .getElementsByClassName('container')[0]
                    .removeAttribute('style');
                document.getElementById('locked').remove();
            }
        }
    },

    mounted() {
        if (!this.fragment) {
            throw new Error("Edit form's data.fragment is not defined!");
        }
        if (!this.model) {
            throw new Error("Edit form's data.model is not defined!");
        }

        this.model.id = this.presetId;

        // prevent user to leave the form if dirty
        window.onbeforeunload = e => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        };

        document.addEventListener('keydown', this.doSave);

        setInterval(this.refreshUpdating, 15000);
        this.refreshUpdating();
    },

    beforeDestroy() {
        document.removeEventListener('keydown', this.doSave);
    },

    $_veeValidate: {
        validator: 'new'
    },

    methods: {
        doSave(e) {
            if (!(e.keyCode === 83 && e.ctrlKey)) {
                return;
            }

            e.preventDefault();
            this.submit();
        },

        goToPage(url, save = true) {
            if (this.isDirty && save) {
                this.submit();
                this.redirUrl = url;
            } else {
                var base_url = document
                    .querySelector('#baseUrl')
                    .getAttribute('value');
                window.location.href = base_url + '/' + url;
            }
        },

        goToAdminPage(url, save = true) {
            this.goToPage('admin/' + url, save);
        },

        reset() {
            for (let field of this._getFieldsFromFragment(this.fragment, {
                includeId: false
            })) {
                let clone = _.cloneDeep(this.model_database[field]);
                Vue.set(this.model, field, clone);
            }
        },

        loadModelDataFromResult(result) {
            // load the requested fields to the vue data.model property
            for (let field of this._getFieldsFromFragment(this.fragment, {
                includeId: false
            })) {
                Vue.set(
                    this.model,
                    field,
                    _.cloneDeep(result.data.model_database[field]) // necessary for nested models
                );
            }
        },

        loadEnumJsonFromResult(result, enumName, vueEnumModel) {
            const data = JSON.parse(result.data.model_database[enumName]);

            for (const [key, value] of Object.entries(data)) {
                let key_parsed = parseInt(key);
                if (isNaN(key_parsed)) {
                    // means we are dealing with an associative array (using strings as keys)
                    key_parsed = key;
                }

                vueEnumModel.push({ value: key_parsed, text: value });
            }
        },

        transformFields(fields, getter_func) {
            for (let field of fields) {
                this.model[field] = getter_func(this.model[field]);
                this.model_database[field] = getter_func(
                    this.model_database[field]
                );
            }
        },

        _getFieldsFromFragment(fragment, options = { includeId: true }) {
            if (!fragment) {
                throw new Error('Expected a fragment, but got none.');
            }

            // here, all the fragments' definitions must be on same data type (see SongLyric.js for example)
            let fieldDefs = fragment.definitions.flatMap(
                def => def.selectionSet.selections
            );

            let fieldNames = fieldDefs.map(field =>
                field.alias ? field.alias.value : field.name.value
            );
            // console.log(fieldNames);

            if (!options.includeId)
                fieldNames = fieldNames.filter(field => field != 'id');

            return fieldNames;
        },

        refreshUpdating() {
            let pathnameSplit = window.location.pathname.split('/');
            let currentModel = pathnameSplit[pathnameSplit.length - 3];
            let models = ['song', 'songbook'];

            if (models.indexOf(currentModel) != -1) {
                axios
                    .get(
                        '/refresh-updating/' +
                            currentModel +
                            '/' +
                            this.presetId
                    )
                    .then(
                        response => (this.isLocked = response.data == 'Locked')
                    );
            }
        }
    }
};
</script>
